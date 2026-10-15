<?php

use App\Application\Payments\UseCases\HandleStripeWebhook;
use App\Events\PaymentConfirmed;
use App\Models\Payment;
use App\Models\RaceRegistration;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake([PaymentConfirmed::class]);
});

function pendingStripePayment(RaceRegistration $registration, string $sessionId): Payment
{
    return Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $registration->pilot->user_id,
        'amount' => 50,
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'method' => 'stripe',
        'status' => 'pending',
        'stripe_session_id' => $sessionId,
    ]);
}

test('unknown payment intent is not assigned to another pending payment with the same amount', function () {
    Config::set('stripe.secret', null);
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_PAYMENT']);
    $payment = pendingStripePayment($registration, 'cs_known');

    $result = app(HandleStripeWebhook::class)->handlePaymentIntentSucceeded([
        'id' => 'pi_unknown',
        'amount' => 5000,
        'amount_received' => 5000,
        'currency' => 'eur',
        'metadata' => [],
    ], 'evt_unknown');

    expect($result)->toBeNull()
        ->and($payment->fresh()->isPending())->toBeTrue()
        ->and($registration->fresh()->status)->toBe('PENDING_PAYMENT');
    Event::assertNotDispatched(PaymentConfirmed::class);
});

test('payment intent amount mismatch cannot confirm a known payment', function () {
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_PAYMENT']);
    $payment = pendingStripePayment($registration, 'cs_known');
    $payment->update(['stripe_payment_intent_id' => 'pi_known']);

    $result = app(HandleStripeWebhook::class)->handlePaymentIntentSucceeded([
        'id' => 'pi_known',
        'amount' => 6000,
        'amount_received' => 6000,
        'currency' => 'eur',
    ], 'evt_wrong_amount');

    expect($result)->toBeNull()
        ->and($payment->fresh()->isPending())->toBeTrue();
    Event::assertNotDispatched(PaymentConfirmed::class);
});

test('paid checkout emits confirmation even when registration is already accepted', function () {
    $registration = RaceRegistration::factory()->accepted()->create();
    $payment = pendingStripePayment($registration, 'cs_accepted');

    app(HandleStripeWebhook::class)->handleCheckoutCompleted([
        'id' => 'cs_accepted',
        'payment_intent' => 'pi_accepted',
        'payment_status' => 'paid',
        'amount_total' => 5000,
        'currency' => 'eur',
    ], 'evt_accepted');

    expect($payment->fresh()->isPaid())->toBeTrue()
        ->and($registration->fresh()->status)->toBe('ACCEPTED');
    Event::assertDispatchedTimes(PaymentConfirmed::class, 1);
});

test('paid payment intent emits confirmation once for an accepted registration', function () {
    $registration = RaceRegistration::factory()->accepted()->create();
    $payment = pendingStripePayment($registration, 'cs_intent');
    $payment->update(['stripe_payment_intent_id' => 'pi_intent']);
    $intent = [
        'id' => 'pi_intent',
        'amount' => 5000,
        'amount_received' => 5000,
        'currency' => 'eur',
    ];

    $handler = app(HandleStripeWebhook::class);
    $handler->handlePaymentIntentSucceeded($intent, 'evt_intent');
    $handler->handlePaymentIntentSucceeded($intent, 'evt_replay');

    expect($payment->fresh()->isPaid())->toBeTrue();
    Event::assertDispatchedTimes(PaymentConfirmed::class, 1);
});

test('checkout confirmation replay preserves a fully refunded payment', function () {
    $registration = RaceRegistration::factory()->accepted()->create();
    $payment = pendingStripePayment($registration, 'cs_refunded');
    $payment->update([
        'status' => 'refunded',
        'stripe_payment_intent_id' => 'pi_refunded',
        'stripe_event_id' => 'evt_original',
        'paid_at' => now()->subDay(),
        'refunded_at' => now(),
        'refund_amount_cents' => 5000,
    ]);
    $receipt = $payment->receipt()->create([
        'number' => 'TEST-0001',
        'issued_at' => now()->subDay(),
        'issuer_name' => 'Association test',
        'issuer_siret' => '00000000000000',
        'issuer_address' => 'Adresse test',
        'issuer_email' => 'test@example.org',
        'issuer_phone' => '0000000000',
        'payer_name' => 'Pilote test',
        'race_name' => 'Course test',
        'amount_cents' => 5000,
        'payment_status_at_issue' => 'paid',
        'currency' => 'EUR',
        'payment_method' => 'stripe',
    ]);

    $handler = app(HandleStripeWebhook::class);
    $handler->handleCheckoutCompleted([
        'id' => 'cs_refunded',
        'payment_intent' => 'pi_refunded',
        'payment_status' => 'paid',
        'amount_total' => 5000,
        'currency' => 'eur',
    ], 'evt_replay');
    $handler->handleCheckoutExpired(['id' => 'cs_refunded']);
    $handler->handlePaymentFailed(['id' => 'pi_refunded']);

    expect($payment->fresh()->isRefunded())->toBeTrue()
        ->and($payment->refund_amount_cents)->toBe(5000)
        ->and($payment->stripe_event_id)->toBe('evt_original')
        ->and($payment->refunded_at)->not->toBeNull()
        ->and($payment->receipt->is($receipt))->toBeTrue()
        ->and($payment->receipt->number)->toBe('TEST-0001');
    Event::assertNotDispatched(PaymentConfirmed::class);
});

test('payment intent confirmation replay preserves a partially refunded payment', function () {
    $registration = RaceRegistration::factory()->accepted()->create();
    $payment = pendingStripePayment($registration, 'cs_partial');
    $payment->update([
        'status' => 'partially_refunded',
        'stripe_payment_intent_id' => 'pi_partial',
        'stripe_event_id' => 'evt_original',
        'paid_at' => now()->subDay(),
        'refunded_at' => now(),
        'refund_amount_cents' => 2000,
    ]);

    app(HandleStripeWebhook::class)->handlePaymentIntentSucceeded([
        'id' => 'pi_partial',
        'amount' => 5000,
        'amount_received' => 5000,
        'currency' => 'eur',
    ], 'evt_replay');

    expect($payment->fresh()->isPartiallyRefunded())->toBeTrue()
        ->and($payment->refund_amount_cents)->toBe(2000)
        ->and($payment->stripe_event_id)->toBe('evt_original')
        ->and($payment->refunded_at)->not->toBeNull();
    Event::assertNotDispatched(PaymentConfirmed::class);
});

test('an older partial refund event cannot reverse a completed refund or change its receipt', function () {
    $registration = RaceRegistration::factory()->accepted()->create();
    $payment = pendingStripePayment($registration, 'cs_refund_order');
    $payment->update([
        'status' => 'paid',
        'stripe_payment_intent_id' => 'pi_refund_order',
        'paid_at' => now()->subDay(),
    ]);
    $receipt = $payment->receipt()->create([
        'number' => 'TEST-ORDER-0001',
        'issued_at' => now()->subDay(),
        'issuer_name' => 'Association test',
        'issuer_siret' => '00000000000000',
        'issuer_address' => 'Adresse test',
        'issuer_email' => 'test@example.org',
        'issuer_phone' => '0000000000',
        'payer_name' => 'Pilote test',
        'race_name' => 'Course test',
        'amount_cents' => 5000,
        'payment_status_at_issue' => 'paid',
        'currency' => 'EUR',
        'payment_method' => 'stripe',
    ]);

    $handler = app(HandleStripeWebhook::class);
    $handler->handleRefund([
        'payment_intent' => 'pi_refund_order',
        'amount' => 5000,
        'amount_refunded' => 5000,
        'currency' => 'eur',
    ]);
    $refundedAt = $payment->fresh()->refunded_at;
    $handler->handleRefund([
        'payment_intent' => 'pi_refund_order',
        'amount' => 5000,
        'amount_refunded' => 2000,
        'currency' => 'eur',
    ]);

    $updated = $payment->fresh();
    expect($updated->isRefunded())->toBeTrue()
        ->and($updated->refund_amount_cents)->toBe(5000)
        ->and($updated->refunded_at->equalTo($refundedAt))->toBeTrue()
        ->and($updated->receipt->is($receipt))->toBeTrue()
        ->and($updated->receipt->number)->toBe('TEST-ORDER-0001');
    Event::assertNotDispatched(PaymentConfirmed::class);
});

test('a refund event with inconsistent monetary details cannot change the payment', function (array $overrides) {
    $registration = RaceRegistration::factory()->accepted()->create();
    $payment = pendingStripePayment($registration, 'cs_bad_refund');
    $payment->update([
        'status' => 'paid',
        'stripe_payment_intent_id' => 'pi_bad_refund',
        'paid_at' => now()->subDay(),
    ]);

    app(HandleStripeWebhook::class)->handleRefund(array_replace([
        'payment_intent' => 'pi_bad_refund',
        'amount' => 5000,
        'amount_refunded' => 2000,
        'currency' => 'eur',
    ], $overrides));

    expect($payment->fresh()->isPaid())->toBeTrue()
        ->and($payment->refund_amount_cents)->toBeNull()
        ->and($payment->refunded_at)->toBeNull();
})->with([
    'missing charge amount' => [['amount' => null]],
    'wrong charge amount' => [['amount' => 6000]],
    'missing currency' => [['currency' => null]],
    'wrong currency' => [['currency' => 'usd']],
    'refund above total' => [['amount_refunded' => 6000]],
    'zero refund' => [['amount_refunded' => 0]],
]);

test('checkout rejects incomplete or mismatched monetary details', function (array $overrides) {
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_PAYMENT']);
    $payment = pendingStripePayment($registration, 'cs_incomplete');
    $payment->update(['stripe_payment_intent_id' => 'pi_expected']);

    app(HandleStripeWebhook::class)->handleCheckoutCompleted(array_replace([
        'id' => 'cs_incomplete',
        'payment_intent' => 'pi_expected',
        'payment_status' => 'paid',
        'amount_total' => 5000,
        'currency' => 'eur',
    ], $overrides), 'evt_incomplete');

    expect($payment->fresh()->isPending())->toBeTrue()
        ->and($registration->fresh()->status)->toBe('PENDING_PAYMENT');
    Event::assertNotDispatched(PaymentConfirmed::class);
})->with([
    'missing amount' => [['amount_total' => null]],
    'zero amount' => [['amount_total' => 0]],
    'missing currency' => [['currency' => null]],
    'wrong currency' => [['currency' => 'usd']],
    'wrong known intent' => [['payment_intent' => 'pi_other']],
]);

test('payment intent rejects incomplete monetary details', function (array $overrides) {
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_PAYMENT']);
    $payment = pendingStripePayment($registration, 'cs_intent_incomplete');
    $payment->update(['stripe_payment_intent_id' => 'pi_incomplete']);

    $result = app(HandleStripeWebhook::class)->handlePaymentIntentSucceeded(array_replace([
        'id' => 'pi_incomplete',
        'amount' => 5000,
        'amount_received' => 5000,
        'currency' => 'eur',
    ], $overrides), 'evt_incomplete');

    expect($result)->toBeNull()
        ->and($payment->fresh()->isPending())->toBeTrue()
        ->and($registration->fresh()->status)->toBe('PENDING_PAYMENT');
    Event::assertNotDispatched(PaymentConfirmed::class);
})->with([
    'missing received amount' => [['amount_received' => null]],
    'zero received amount' => [['amount_received' => 0]],
    'missing currency' => [['currency' => null]],
    'invalid currency' => [['currency' => 'EU']],
    'wrong currency' => [['currency' => 'usd']],
]);
