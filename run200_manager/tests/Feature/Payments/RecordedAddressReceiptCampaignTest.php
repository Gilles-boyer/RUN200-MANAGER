<?php

use App\Application\Payments\UseCases\IssuePaymentReceipt;
use App\Application\Payments\UseCases\SendPaymentReceipt;
use App\Infrastructure\Payments\Stripe\HistoricalStripePaymentLookup;
use App\Mail\PaymentConfirmed;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Stripe\Charge;
use Stripe\PaymentIntent;

beforeEach(function () {
    config()->set('payment_receipts.auto_send', true);
    config()->set('payment_receipts.historical_delivery.mode', 'pilot');
});

function recordedCampaignPayment(string $method = 'cash', array $changes = [], ?RaceRegistration $registration = null): Payment
{
    $registration ??= RaceRegistration::factory()->create();
    $registration->pilot->user->forceFill(['email_verified_at' => null])->save();
    $staff = User::factory()->create();

    return Payment::create(array_merge([
        'race_registration_id' => $registration->id,
        'user_id' => $method === 'stripe' ? $registration->pilot->user_id : $staff->id,
        'amount' => '50.00',
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'method' => $method,
        'status' => 'paid',
        'paid_at' => now()->subDays(10),
        'stripe_payment_intent_id' => $method === 'stripe' ? 'pi_recorded_'.$registration->id : null,
    ], $changes));
}

function recordedCampaignHash(string $paymentIds): string
{
    Artisan::call('payment-receipts:send-recorded-addresses', ['--payment-ids' => $paymentIds]);
    $output = Artisan::output();
    expect($output)->toMatch('/Empreinte de revue : [a-f0-9]{64}/');
    preg_match('/Empreinte de revue : ([a-f0-9]{64})/', $output, $matches);

    return $matches[1];
}

function recordedCampaignStripeLookup(Payment $payment, array $intentChanges = [], array $chargeChanges = []): void
{
    $charge = Charge::constructFrom(array_merge([
        'id' => 'ch_recorded_'.$payment->id,
        'object' => 'charge',
        'payment_intent' => $payment->stripe_payment_intent_id,
        'livemode' => true,
        'paid' => true,
        'captured' => true,
        'disputed' => false,
        'amount' => $payment->amount_cents,
        'amount_refunded' => 0,
        'currency' => 'eur',
    ], $chargeChanges));
    $intent = PaymentIntent::constructFrom(array_merge([
        'id' => $payment->stripe_payment_intent_id,
        'object' => 'payment_intent',
        'livemode' => true,
        'status' => 'succeeded',
        'amount' => $payment->amount_cents,
        'amount_received' => $payment->amount_cents,
        'currency' => 'eur',
        'metadata' => [
            'registration_id' => (string) $payment->race_registration_id,
            'pilot_id' => (string) $payment->registration->pilot_id,
        ],
        'latest_charge' => $charge,
    ], $intentChanges));
    app()->instance(HistoricalStripePaymentLookup::class, Mockery::mock(HistoricalStripePaymentLookup::class)
        ->shouldReceive('retrieve')->with($payment->stripe_payment_intent_id)
        ->andReturn($intent)->getMock());
}

test('reviewed non Stripe payments send to each unverified pilot and never to recording staff', function () {
    Mail::fake();
    $first = recordedCampaignPayment('cash');
    $second = recordedCampaignPayment('card_onsite', ['amount' => '25.00', 'amount_cents' => 2500]);
    $ids = $first->id.','.$second->id;
    $hash = recordedCampaignHash($ids);

    expect(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
    expect(Artisan::call('payment-receipts:send-recorded-addresses', [
        '--payment-ids' => $ids, '--commit' => true, '--review-hash' => $hash,
    ]))->toBe(0);

    expect(PaymentReceipt::count())->toBe(2)
        ->and(PaymentReceipt::query()->where('email_status', 'sent')->count())->toBe(2)
        ->and(config('payment_receipts.historical_delivery.mode'))->toBe('pilot');
    Mail::assertSent(PaymentConfirmed::class, 2);
    Mail::assertSent(PaymentConfirmed::class, fn ($mail) => $mail->hasTo($first->registration->pilot->user->email)
        && ! $mail->hasTo($first->user->email) && ! $mail->hasTo($second->user->email));
    Mail::assertSent(PaymentConfirmed::class, fn ($mail) => $mail->hasTo($second->registration->pilot->user->email)
        && ! $mail->hasTo($first->user->email) && ! $mail->hasTo($second->user->email));
});

test('multiple collected payments for one registration are held for reconciliation', function () {
    Mail::fake();
    $registration = RaceRegistration::factory()->create();
    $first = recordedCampaignPayment('cash', [], $registration);
    $second = recordedCampaignPayment('card_onsite', ['amount' => '25.00', 'amount_cents' => 2500], $registration);

    expect(Artisan::call('payment-receipts:send-recorded-addresses', [
        '--payment-ids' => $first->id.','.$second->id,
    ]))->toBe(1);
    expect(Artisan::output())->toContain('multiple_collected_payments');
    expect(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
});

test('a changed dossier address invalidates the reviewed fingerprint before any send', function () {
    Mail::fake();
    $payment = recordedCampaignPayment('bank_transfer');
    $hash = recordedCampaignHash((string) $payment->id);
    $payment->registration->pilot->user->update(['email' => 'new-address@example.test']);

    expect(Artisan::call('payment-receipts:send-recorded-addresses', [
        '--payment-ids' => (string) $payment->id, '--commit' => true, '--review-hash' => $hash,
    ]))->toBe(1);
    expect(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();

    $newHash = recordedCampaignHash((string) $payment->id);
    expect($newHash)->not->toBe($hash);
    expect(Artisan::call('payment-receipts:send-recorded-addresses', [
        '--payment-ids' => (string) $payment->id, '--commit' => true, '--review-hash' => $newHash,
    ]))->toBe(0);
    Mail::assertSent(PaymentConfirmed::class, fn ($mail) => $mail->hasTo('new-address@example.test'));
    Mail::assertSent(PaymentConfirmed::class, 1);
});

test('changed pilot and race PDF identities invalidate the reviewed batch before any send', function () {
    Mail::fake();
    $pilotChanged = recordedCampaignPayment('cash');
    $raceChanged = recordedCampaignPayment('bank_transfer');
    $ids = $pilotChanged->id.','.$raceChanged->id;
    $hash = recordedCampaignHash($ids);

    $pilotChanged->registration->pilot->update(['last_name' => 'Changed']);
    $raceChanged->registration->race->update(['name' => 'Changed Race']);

    expect(Artisan::call('payment-receipts:send-recorded-addresses', [
        '--payment-ids' => $ids, '--commit' => true, '--review-hash' => $hash,
    ]))->toBe(1);
    expect(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
});

test('an existing receipt keeps its original identity snapshot while delivery uses the current dossier address', function () {
    Mail::fake();
    $payment = recordedCampaignPayment('cash');
    $receipt = app(IssuePaymentReceipt::class)->issue($payment, historical: true);
    $originalEmail = $receipt->payer_email;
    $payment->registration->pilot->user->update(['email' => 'current-address@example.test']);
    $hash = recordedCampaignHash((string) $payment->id);

    expect(Artisan::call('payment-receipts:send-recorded-addresses', [
        '--payment-ids' => (string) $payment->id, '--commit' => true, '--review-hash' => $hash,
    ]))->toBe(0);
    expect($receipt->fresh()->payer_email)->toBe($originalEmail)
        ->and($receipt->fresh()->delivery_email)->toBe('current-address@example.test')
        ->and($receipt->fresh()->email_status)->toBe('sent');
    Mail::assertSent(PaymentConfirmed::class, fn ($mail) => $mail->hasTo('current-address@example.test'));
    Mail::assertSent(PaymentConfirmed::class, 1);
});

test('Stripe live checks permit a matching unverified dossier but hold missing identity metadata', function () {
    Mail::fake();
    $payment = recordedCampaignPayment('stripe');
    recordedCampaignStripeLookup($payment);
    $hash = recordedCampaignHash((string) $payment->id);
    expect(Artisan::call('payment-receipts:send-recorded-addresses', [
        '--payment-ids' => (string) $payment->id, '--commit' => true, '--review-hash' => $hash,
    ]))->toBe(0);
    Mail::assertSent(PaymentConfirmed::class, 1);

    $other = recordedCampaignPayment('stripe');
    recordedCampaignStripeLookup($other, ['metadata' => []]);
    Artisan::call('payment-receipts:send-recorded-addresses', ['--payment-ids' => (string) $other->id]);
    expect(Artisan::output())->toContain('stripe_intent_mismatch');
    expect(PaymentReceipt::query()->where('payment_id', $other->id)->exists())->toBeFalse();
    Mail::assertSent(PaymentConfirmed::class, 1);
});

test('already sent receipts are skipped and uncertain SMTP outcomes hold the batch', function () {
    Mail::fake();
    $sentPayment = recordedCampaignPayment('manual');
    $sentReceipt = app(IssuePaymentReceipt::class)->issue($sentPayment, historical: true);
    $sentReceipt->update(['email_status' => 'sent', 'email_sent_at' => now()]);
    $uncertain = recordedCampaignPayment('cash');
    $uncertainReceipt = app(IssuePaymentReceipt::class)->issue($uncertain, historical: true);
    $uncertainReceipt->update(['email_status' => 'sending']);
    $ids = $sentPayment->id.','.$uncertain->id;
    $hash = recordedCampaignHash($ids);

    expect(Artisan::call('payment-receipts:send-recorded-addresses', [
        '--payment-ids' => $ids, '--commit' => true, '--review-hash' => $hash,
    ]))->toBe(1);
    expect($sentReceipt->fresh()->email_status)->toBe('sent')
        ->and($uncertainReceipt->fresh()->email_status)->toBe('sending');
    Mail::assertNothingOutgoing();
});

test('non Stripe missing paid_at or inconsistent amounts remain held', function () {
    Mail::fake();
    $missingDate = recordedCampaignPayment('cash', ['paid_at' => null]);
    $badAmount = recordedCampaignPayment('manual', ['amount' => '75.00', 'amount_cents' => 5000]);
    Artisan::call('payment-receipts:send-recorded-addresses', [
        '--payment-ids' => $missingDate->id.','.$badAmount->id,
    ]);
    expect(Artisan::output())->toContain('paid_at_missing_or_future', 'amount_mismatch')
        ->and(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
});

test('future paid_at legacy cents refund mismatch and invalid addresses remain held', function () {
    Mail::fake();
    $future = recordedCampaignPayment('cash', ['paid_at' => now()->addDay()]);
    $legacy = recordedCampaignPayment('bank_transfer', ['amount_cents' => 0]);
    $refund = recordedCampaignPayment('manual', ['status' => 'partially_refunded', 'refund_amount_cents' => 0]);
    $invalidAddress = recordedCampaignPayment('card_onsite');
    $invalidAddress->registration->pilot->user->update(['email' => 'invalid-address']);

    expect(Artisan::call('payment-receipts:send-recorded-addresses', [
        '--payment-ids' => implode(',', [$future->id, $legacy->id, $refund->id, $invalidAddress->id]),
    ]))->toBe(1);
    expect(Artisan::output())->toContain(
        'paid_at_missing_or_future',
        'legacy_amount_requires_review',
        'payment_snapshot_invalid',
        'pilot_or_recorded_address_missing',
    );
    expect(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
});

test('zero or negative decimal amount cannot be overruled by positive cents', function () {
    Mail::fake();
    $zero = recordedCampaignPayment('cash', ['amount' => '0.00', 'amount_cents' => 5000]);
    $negative = recordedCampaignPayment('manual', ['amount' => '-10.00', 'amount_cents' => 5000]);
    $ids = $zero->id.','.$negative->id;
    $hash = recordedCampaignHash($ids);

    Artisan::call('payment-receipts:send-recorded-addresses', ['--payment-ids' => $ids]);
    expect(Artisan::output())->toContain('amount_mismatch');
    expect(Artisan::call('payment-receipts:send-recorded-addresses', [
        '--payment-ids' => $ids, '--commit' => true, '--review-hash' => $hash,
    ]))->toBe(1);
    expect(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
});

test('already sent receipts are idempotent when explicitly reviewed again', function () {
    Mail::fake();
    $payment = recordedCampaignPayment('cash');
    $receipt = app(IssuePaymentReceipt::class)->issue($payment, historical: true);
    $receipt->update(['email_status' => 'sent', 'email_sent_at' => now()]);
    $hash = recordedCampaignHash((string) $payment->id);

    expect(Artisan::call('payment-receipts:send-recorded-addresses', [
        '--payment-ids' => (string) $payment->id, '--commit' => true, '--review-hash' => $hash,
    ]))->toBe(0);
    expect(PaymentReceipt::count())->toBe(1);
    Mail::assertNothingOutgoing();
});

test('batch requires at most 20 explicit IDs and a matching review hash', function () {
    Mail::fake();
    $payment = recordedCampaignPayment();
    expect(Artisan::call('payment-receipts:send-recorded-addresses', [
        '--payment-ids' => implode(',', range(1, 21)),
    ]))->toBe(1);
    expect(Artisan::call('payment-receipts:send-recorded-addresses', [
        '--payment-ids' => $payment->id.','.$payment->id,
    ]))->toBe(1);
    expect(Artisan::call('payment-receipts:send-recorded-addresses', [
        '--payment-ids' => (string) $payment->id, '--commit' => true,
    ]))->toBe(1);
    expect(PaymentReceipt::count())->toBe(0);
    Mail::assertNothingOutgoing();
});

test('sender rejects a changed expected recipient even when email verification is not required', function () {
    Mail::fake();
    $payment = recordedCampaignPayment();
    $recipient = $payment->registration->pilot->user;
    $receipt = app(IssuePaymentReceipt::class)->issue($payment, historical: true);

    expect(app(SendPaymentReceipt::class)->send(
        $receipt,
        historical: true,
        expectedEmail: 'former-address@example.test',
        expectedUserId: $recipient->id,
    ))->toBeFalse();
    expect($receipt->fresh()->email_status)->toBe('failed')
        ->and($receipt->fresh()->email_last_error)->toBe('recipient_changed');
    Mail::assertNothingOutgoing();
});
