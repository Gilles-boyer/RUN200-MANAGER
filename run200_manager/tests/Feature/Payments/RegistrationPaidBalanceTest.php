<?php

use App\Models\Payment;
use App\Models\RaceRegistration;

test('registration paid balance includes partial refunds and historical decimal amounts', function () {
    $registration = RaceRegistration::factory()->create();

    Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $registration->pilot->user_id,
        'amount' => '50.00',
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'method' => 'stripe',
        'status' => 'partially_refunded',
        'refund_amount_cents' => 2000,
    ]);

    Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $registration->pilot->user_id,
        'amount' => '12.50',
        'amount_cents' => 0,
        'currency' => 'EUR',
        'method' => 'manual',
        'status' => 'paid',
    ]);

    expect($registration->getPaidAmountCents())->toBe(4250)
        ->and($registration->isPaid())->toBeTrue();
});

test('registration is no longer paid after its only payment is fully refunded', function () {
    $registration = RaceRegistration::factory()->create();

    Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $registration->pilot->user_id,
        'amount' => '50.00',
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'method' => 'stripe',
        'status' => 'refunded',
        'refund_amount_cents' => 5000,
    ]);

    expect($registration->getPaidAmountCents())->toBe(0)
        ->and($registration->isPaid())->toBeFalse();
});
