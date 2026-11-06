<?php

declare(strict_types=1);

use App\Application\Payments\UseCases\IssuePaymentReceipt;
use App\Application\Payments\UseCases\SendPaymentReceipt;
use App\Application\Payments\UseCases\VoidRecordedPayment;
use App\Events\PaymentConfirmed;
use App\Listeners\SendECardAfterPayment;
use App\Models\Payment;
use App\Models\RaceRegistration;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function recordedPaymentForVoid(array $attributes = [], ?RaceRegistration $registration = null): Payment
{
    $registration ??= RaceRegistration::factory()->accepted()->create();

    return Payment::create(array_merge([
        'race_registration_id' => $registration->id,
        'user_id' => User::factory()->create()->id,
        'amount' => '100.00',
        'amount_cents' => 10000,
        'currency' => 'EUR',
        'method' => 'cash',
        'status' => 'paid',
        'paid_at' => now()->subMinute(),
    ], $attributes));
}

function recordedPaymentSnapshot(Payment $payment): array
{
    $receipt = $payment->receipt()->first();

    return [
        'payment_id' => $payment->id,
        'registration_id' => $payment->race_registration_id,
        'pilot_id' => $payment->registration->pilot_id,
        'status' => $payment->status->value,
        'method' => $payment->method->value,
        'amount_cents' => $payment->amount_cents,
        'receipt_id' => $receipt?->id,
        'receipt_email_status' => $receipt?->email_status,
        'receipt_email_sent_at' => $receipt?->email_sent_at?->toISOString(),
    ];
}

function paymentVoidAdmin(): User
{
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');

    return $admin;
}

test('an authorized correction voids a local payment and unsent receipt without changing accepted registration', function (): void {
    Mail::fake();
    $payment = recordedPaymentForVoid();
    $receipt = app(IssuePaymentReceipt::class)->issue($payment);
    $paidAt = $payment->paid_at;
    $admin = paymentVoidAdmin();

    $result = app(VoidRecordedPayment::class)->execute(
        $payment, $admin, 'Aucun règlement reçu : saisie effectuée par erreur.', recordedPaymentSnapshot($payment)
    );

    expect($result->status->value)->toBe('cancelled')
        ->and($result->paid_at->equalTo($paidAt))->toBeTrue()
        ->and($result->metadata['voided_by'])->toBe($admin->id)
        ->and($result->metadata['void_reason'])->toContain('Aucun règlement')
        ->and($result->metadata['void_receipt_followup_required'])->toBeFalse()
        ->and($receipt->fresh()->email_status)->toBe('voided')
        ->and($payment->registration->fresh()->status)->toBe('ACCEPTED')
        ->and($payment->registration->getPaidAmountCents())->toBe(0);

    expect(app(SendPaymentReceipt::class)->send($receipt))->toBeFalse();
    Mail::assertNothingSent();
    $this->actingAs($admin)->get(route('payments.receipt', $payment))->assertNotFound();
    $this->assertDatabaseHas('activity_log', [
        'subject_type' => Payment::class,
        'subject_id' => $payment->id,
        'causer_id' => $admin->id,
        'description' => 'payment.manual.voided',
    ]);
});

test('a payment-only registration returns to awaiting payment while an accepted one stays accepted', function (): void {
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_VALIDATION']);
    $payment = recordedPaymentForVoid(registration: $registration);

    app(VoidRecordedPayment::class)->execute(
        $payment, paymentVoidAdmin(), 'Paiement déclaré reçu par erreur.', recordedPaymentSnapshot($payment)
    );

    expect($registration->fresh()->status)->toBe('PENDING_PAYMENT');
});

test('another settled payment keeps the registration paid after one correction', function (): void {
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_VALIDATION']);
    $payment = recordedPaymentForVoid(registration: $registration);
    recordedPaymentForVoid(['amount' => '25.00', 'amount_cents' => 2500], $registration);

    app(VoidRecordedPayment::class)->execute(
        $payment, paymentVoidAdmin(), 'Première ligne enregistrée par erreur.', recordedPaymentSnapshot($payment)
    );

    expect($registration->fresh()->status)->toBe('PENDING_VALIDATION')
        ->and($registration->getPaidAmountCents())->toBe(2500);
});

test('a sent receipt remains sent and requires durable follow-up after correction', function (): void {
    $payment = recordedPaymentForVoid();
    $receipt = app(IssuePaymentReceipt::class)->issue($payment);
    $receipt->update(['email_status' => 'sent', 'email_sent_at' => now()]);

    $result = app(VoidRecordedPayment::class)->execute(
        $payment, paymentVoidAdmin(), 'Aucun règlement reçu : erreur de pointage.', recordedPaymentSnapshot($payment)
    );

    expect($result->metadata['void_receipt_followup_required'])->toBeTrue()
        ->and($receipt->fresh()->email_status)->toBe('sent')
        ->and($receipt->email_sent_at)->not->toBeNull();
});

test('receipt sent after modal opening prevents void and forces a fresh review', function (): void {
    $payment = recordedPaymentForVoid();
    $receipt = app(IssuePaymentReceipt::class)->issue($payment);
    $snapshot = recordedPaymentSnapshot($payment);
    $receipt->update(['email_status' => 'sent', 'email_sent_at' => now()]);

    expect(fn () => app(VoidRecordedPayment::class)->execute(
        $payment, paymentVoidAdmin(), 'Paiement déclaré reçu par erreur.', $snapshot
    ))->toThrow(InvalidArgumentException::class, 'reçu a changé');
    expect($payment->fresh()->status->value)->toBe('paid')
        ->and($receipt->fresh()->email_status)->toBe('sent');
});

test('uncertain receipt delivery blocks correction without changing the payment', function (string $emailStatus): void {
    $payment = recordedPaymentForVoid();
    $receipt = app(IssuePaymentReceipt::class)->issue($payment);
    $receipt->update(['email_status' => $emailStatus]);

    expect(fn () => app(VoidRecordedPayment::class)->execute(
        $payment, paymentVoidAdmin(), 'Paiement déclaré reçu par erreur.', recordedPaymentSnapshot($payment)
    ))->toThrow(InvalidArgumentException::class, 'incertain');

    expect($payment->fresh()->status->value)->toBe('paid')
        ->and($receipt->fresh()->email_status)->toBe($emailStatus);
})->with(['sending', 'failed']);

test('a staff payment manager cannot void a paid payment', function (): void {
    $payment = recordedPaymentForVoid();
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');

    expect($staff->can('payment.manage'))->toBeTrue()
        ->and($staff->can('payment.void'))->toBeFalse();
    expect(fn () => app(VoidRecordedPayment::class)->execute(
        $payment, $staff, 'Paiement déclaré reçu par erreur.', recordedPaymentSnapshot($payment)
    ))->toThrow(AuthorizationException::class);
    expect($payment->fresh()->status->value)->toBe('paid');
});

test('stripe or a stripe reference can never be voided through the local correction', function (array $attributes): void {
    $payment = recordedPaymentForVoid($attributes);

    expect(fn () => app(VoidRecordedPayment::class)->execute(
        $payment, paymentVoidAdmin(), 'Paiement déclaré reçu par erreur.', recordedPaymentSnapshot($payment)
    ))->toThrow(InvalidArgumentException::class, 'ne peut pas');
    expect($payment->fresh()->status->value)->toBe('paid');
})->with([
    [['method' => 'stripe']],
    [['stripe_session_id' => 'cs_test_123']],
    [['stripe_payment_intent_id' => 'pi_test_123']],
    [['stripe_customer_id' => 'cus_test_123']],
    [['stripe_event_id' => 'evt_test_123']],
]);

test('a stale snapshot or repeat confirmation never voids a different state', function (): void {
    $payment = recordedPaymentForVoid();
    $admin = paymentVoidAdmin();
    $snapshot = recordedPaymentSnapshot($payment);
    $changed = $snapshot;
    $changed['amount_cents'] = 9000;

    expect(fn () => app(VoidRecordedPayment::class)->execute(
        $payment, $admin, 'Paiement déclaré reçu par erreur.', $changed
    ))->toThrow(InvalidArgumentException::class, 'a changé');
    expect($payment->fresh()->status->value)->toBe('paid');

    app(VoidRecordedPayment::class)->execute($payment, $admin, 'Paiement déclaré reçu par erreur.', $snapshot);
    expect(fn () => app(VoidRecordedPayment::class)->execute(
        $payment, $admin, 'Paiement déclaré reçu par erreur.', $snapshot
    ))->toThrow(InvalidArgumentException::class, 'a changé');
    $this->assertDatabaseCount('payment_receipts', 0);
});

test('a pilot reassignment after opening the modal blocks correction', function (): void {
    $payment = recordedPaymentForVoid();
    $snapshot = recordedPaymentSnapshot($payment);
    $payment->registration->update(['pilot_id' => RaceRegistration::factory()->create()->pilot_id]);

    expect(fn () => app(VoidRecordedPayment::class)->execute(
        $payment, paymentVoidAdmin(), 'Paiement déclaré reçu par erreur.', $snapshot
    ))->toThrow(InvalidArgumentException::class, 'a changé');
    expect($payment->fresh()->status->value)->toBe('paid');
});

test('a cancelled source cannot send a receipt still marked pending', function (): void {
    Mail::fake();
    $payment = recordedPaymentForVoid();
    $receipt = app(IssuePaymentReceipt::class)->issue($payment);
    $payment->update(['status' => 'cancelled']);

    expect(app(SendPaymentReceipt::class)->send($receipt))->toBeFalse()
        ->and($receipt->fresh()->email_status)->toBe('pending');
    Mail::assertNothingSent();
});

test('a cancelled payment does not trigger a delayed E-Card or receipt', function (): void {
    Mail::fake();
    $payment = recordedPaymentForVoid();
    app(VoidRecordedPayment::class)->execute(
        $payment, paymentVoidAdmin(), 'Paiement déclaré reçu par erreur.', recordedPaymentSnapshot($payment)
    );

    app(SendECardAfterPayment::class)->handle(new PaymentConfirmed($payment));
    Mail::assertNothingSent();
});
