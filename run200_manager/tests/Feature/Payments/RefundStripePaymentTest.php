<?php

use App\Application\Payments\UseCases\RefundStripePayment;
use App\Infrastructure\Payments\Stripe\StripePaymentService;
use App\Livewire\Staff\Registrations\PaymentManager;
use App\Models\Payment;
use App\Models\RaceRegistration;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Stripe\Refund;

function refundablePayment(array $attributes = []): Payment
{
    $registration = RaceRegistration::factory()->accepted()->create();

    return Payment::create(array_merge([
        'race_registration_id' => $registration->id,
        'user_id' => $registration->pilot->user_id,
        'amount' => '50.00',
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'method' => 'stripe',
        'status' => 'paid',
        'paid_at' => now(),
        'stripe_payment_intent_id' => 'pi_test_refund',
    ], $attributes));
}

test('Stripe refund uses enum status and refunds only the remaining amount', function () {
    $payment = refundablePayment();
    $operator = User::factory()->create();
    $keys = [];
    $stripe = Mockery::mock(StripePaymentService::class);
    $stripe->shouldReceive('refund')->twice()->andReturnUsing(function ($intent, $amount, $reason, $key) use (&$keys) {
        expect($intent)->toBe('pi_test_refund')->and($reason)->toBeNull();
        $keys[] = $key;

        return Refund::constructFrom(['id' => 're_'.$amount, 'status' => 'succeeded']);
    });
    $useCase = new RefundStripePayment($stripe);

    $first = $useCase->execute($payment, $operator, 2000, 'Motif saisi par le staff');
    $second = $useCase->execute($first, $operator);

    expect($first->status->value)->toBe('partially_refunded')
        ->and($first->refund_amount_cents)->toBe(2000)
        ->and($second->status->value)->toBe('refunded')
        ->and($second->refund_amount_cents)->toBe(5000)
        ->and($second->metadata['refunds'])->toHaveCount(2)
        ->and($keys[0])->not->toBe($keys[1]);
    $stripe->shouldHaveReceived('refund')->with('pi_test_refund', 3000, null, Mockery::type('string'));
});

test('retry after a transport error reuses the Stripe idempotency key', function () {
    $payment = refundablePayment();
    $operator = User::factory()->create();
    $keys = [];
    $stripe = Mockery::mock(StripePaymentService::class);
    $stripe->shouldReceive('refund')->twice()->andReturnUsing(function ($intent, $amount, $reason, $key) use (&$keys) {
        $keys[] = $key;
        if (count($keys) === 1) {
            throw new RuntimeException('Network unavailable');
        }

        return Refund::constructFrom(['id' => 're_retry', 'status' => 'succeeded']);
    });
    $useCase = new RefundStripePayment($stripe);

    expect(fn () => $useCase->execute($payment, $operator, 1500))->toThrow(RuntimeException::class);
    expect($payment->fresh()->metadata['refund_pending']['amount_cents'])->toBe(1500);
    $result = $useCase->execute($payment, $operator, 1500);

    expect($result->refund_amount_cents)->toBe(1500)
        ->and($keys[0])->toBe($keys[1])
        ->and($result->metadata)->not->toHaveKey('refund_pending');
});

test('Stripe service forwards the idempotency key and omits free-form reason', function () {
    config(['stripe.secret' => 'sk_test_receipt_unit']);
    $previous = ApiRequestor::httpClient();
    $client = new class implements ClientInterface
    {
        public array $headers = [];

        public array $params = [];

        public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
        {
            $this->headers = $headers;
            $this->params = $params;

            return ['{"id":"re_test_http","object":"refund","status":"succeeded","amount":1500}', 200, ['Content-Type' => 'application/json']];
        }
    };
    ApiRequestor::setHttpClient($client);

    try {
        $result = (new StripePaymentService)->refund('pi_test_refund', 1500, 'Motif libre', 'receipt-refund-test-key');
        expect($result->id)->toBe('re_test_http')
            ->and($client->headers)->toContain('Idempotency-Key: receipt-refund-test-key')
            ->and($client->params)->not->toHaveKey('reason');
    } finally {
        ApiRequestor::setHttpClient($previous);
    }
});

test('staff without refund permission and foreign registration payment IDs are refused', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $first = refundablePayment();
    $other = refundablePayment(['status' => 'pending']);
    $this->actingAs($staff);

    Livewire::test(PaymentManager::class, ['registration' => $first->registration])
        ->call('openRefundModal', $first->id)
        ->assertForbidden();

    Livewire::test(PaymentManager::class, ['registration' => $first->registration])
        ->call('cancelPayment', $other->id)
        ->assertNotFound();

    expect($other->fresh()->status->value)->toBe('pending');
});

test('payment manager requires view access and manage permission for manual actions', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $pilot = User::factory()->create();
    $staff = User::factory()->create();
    $viewer = Role::firstOrCreate(['name' => 'PAYMENT_VIEWER', 'guard_name' => 'web']);
    $viewer->givePermissionTo('payment.view');
    $staff->assignRole($viewer);
    $registration = RaceRegistration::factory()->accepted()->create();
    $payment = Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $registration->pilot->user_id,
        'amount' => '50.00', 'amount_cents' => 5000, 'currency' => 'EUR',
        'method' => 'cash', 'status' => 'pending',
    ]);

    $this->actingAs($pilot);
    Livewire::test(PaymentManager::class, ['registration' => $registration])->assertForbidden();

    $this->actingAs($staff);
    Livewire::test(PaymentManager::class, ['registration' => $registration])
        ->call('cancelPayment', $payment->id)->assertForbidden();
    Livewire::test(PaymentManager::class, ['registration' => $registration])
        ->call('openManualPaymentModal')->assertForbidden();
    Livewire::test(PaymentManager::class, ['registration' => $registration])
        ->set('manualAmount', '50,00')->call('recordManualPayment')->assertForbidden();
    expect($payment->fresh()->status->value)->toBe('pending');
});
