<?php

use App\Application\Payments\UseCases\RecordManualPayment;
use App\Events\PaymentConfirmed;
use App\Models\Payment;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Event;

function pendingWalkInPayment(RaceRegistration $registration, string $method = 'cash'): Payment
{
    return Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $registration->pilot->user_id,
        'amount' => '50.00',
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'method' => $method,
        'status' => 'pending',
    ]);
}

test('a pending walk-in payment is confirmed on the same row and only once', function () {
    Event::fake([PaymentConfirmed::class]);
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_PAYMENT']);
    $pending = pendingWalkInPayment($registration);
    $recorder = User::factory()->create();
    $useCase = app(RecordManualPayment::class);

    $first = $useCase->execute($registration, $recorder, 50.00, 'EUR', 'Espèces reçues', 'cash');
    $second = $useCase->execute($registration, $recorder, 50.00, 'EUR', 'Espèces reçues', 'cash');

    expect($first->id)->toBe($pending->id)
        ->and($second->id)->toBe($pending->id)
        ->and($first->fresh()->isPaid())->toBeTrue()
        ->and($registration->fresh()->status)->toBe('PENDING_VALIDATION')
        ->and($registration->payments()->count())->toBe(1);
    Event::assertDispatchedTimes(PaymentConfirmed::class, 1);
});

test('a pending Stripe checkout cannot be silently replaced by manual payment', function () {
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_PAYMENT']);
    $stripe = pendingWalkInPayment($registration, 'stripe');

    expect(fn () => app(RecordManualPayment::class)->execute(
        $registration, User::factory()->create(), 50.00, 'EUR', null, 'cash'
    ))->toThrow(InvalidArgumentException::class, 'Stripe est en cours');

    expect($registration->fresh()->status)->toBe('PENDING_PAYMENT')
        ->and($stripe->fresh()->isPending())->toBeTrue()
        ->and($registration->payments()->count())->toBe(1);
});

test('a conflicting replay does not alter the confirmed payment', function () {
    Event::fake([PaymentConfirmed::class]);
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_PAYMENT']);
    $payment = pendingWalkInPayment($registration);
    $recorder = User::factory()->create();
    $useCase = app(RecordManualPayment::class);
    $useCase->execute($registration, $recorder, 50.00, 'EUR', null, 'cash');

    expect(fn () => $useCase->execute($registration, $recorder, 75.00, 'EUR', null, 'cash'))
        ->toThrow(InvalidArgumentException::class, 'déjà un paiement validé');

    expect($payment->fresh()->amount_cents)->toBe(5000);
    Event::assertDispatchedTimes(PaymentConfirmed::class, 1);
});
