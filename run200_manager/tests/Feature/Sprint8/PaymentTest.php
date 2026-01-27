<?php

declare(strict_types=1);

namespace Tests\Feature\Sprint8;

use App\Application\Payments\UseCases\RecordManualPayment;
use App\Domain\Payment\Enums\PaymentMethod;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Infrastructure\Payments\Stripe\StripePaymentService;
use App\Models\Payment;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $roles = ['ADMIN', 'PILOTE', 'STAFF_ADMINISTRATIF', 'CONTROLEUR_TECHNIQUE', 'STAFF_ENTREE', 'STAFF_SONO'];
    foreach ($roles as $role) {
        Role::findOrCreate($role, 'web');
    }

    $this->testUser = User::factory()->create();
});

// Helper function to create a payment with required user_id
function createPayment(array $attributes = []): Payment
{
    $defaults = [
        'race_registration_id' => RaceRegistration::factory()->create()->id,
        'user_id' => User::factory()->create()->id,
        'amount' => 50.00,
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'method' => 'stripe',
        'status' => 'pending',
    ];

    return Payment::create(array_merge($defaults, $attributes));
}

// ============================================================================
// PaymentStatus Enum Tests
// ============================================================================

test('payment status enum has correct values', function () {
    expect(PaymentStatus::PENDING->value)->toBe('pending')
        ->and(PaymentStatus::PROCESSING->value)->toBe('processing')
        ->and(PaymentStatus::PAID->value)->toBe('paid')
        ->and(PaymentStatus::FAILED->value)->toBe('failed')
        ->and(PaymentStatus::REFUNDED->value)->toBe('refunded')
        ->and(PaymentStatus::CANCELLED->value)->toBe('cancelled');
});

test('payment status has correct labels', function () {
    expect(PaymentStatus::PENDING->label())->toBe('En attente')
        ->and(PaymentStatus::PAID->label())->toBe('Payé')
        ->and(PaymentStatus::REFUNDED->label())->toBe('Remboursé');
});

// ============================================================================
// PaymentMethod Enum Tests
// ============================================================================

test('payment method enum has correct values', function () {
    expect(PaymentMethod::MANUAL->value)->toBe('manual')
        ->and(PaymentMethod::STRIPE->value)->toBe('stripe');
});

test('payment method has correct labels', function () {
    expect(PaymentMethod::MANUAL->label())->toBe('Paiement manuel')
        ->and(PaymentMethod::STRIPE->label())->toContain('Paiement');
});

// ============================================================================
// RecordManualPayment Use Case Tests
// ============================================================================

test('record manual payment creates payment record', function () {
    $registration = RaceRegistration::factory()->accepted()->create();
    $staffUser = User::factory()->create();
    $staffUser->assignRole('STAFF_ADMINISTRATIF');

    $useCase = new RecordManualPayment;
    $payment = $useCase->execute($registration, $staffUser, 50.00, 'EUR', 'Paiement en espèces');

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->race_registration_id)->toBe($registration->id)
        ->and($payment->amount_cents)->toBe(5000)
        ->and($payment->status)->toBe('paid')
        ->and($payment->method)->toBe('manual');
});

test('record manual payment rejects non-accepted registration', function () {
    $registration = RaceRegistration::factory()->pending()->create();
    $staffUser = User::factory()->create();
    $staffUser->assignRole('STAFF_ADMINISTRATIF');

    $useCase = new RecordManualPayment;

    expect(fn () => $useCase->execute($registration, $staffUser, 50.00))
        ->toThrow(\InvalidArgumentException::class);
});

test('record manual payment rejects duplicate payment', function () {
    $registration = RaceRegistration::factory()->accepted()->create();
    $staffUser = User::factory()->create();
    $staffUser->assignRole('STAFF_ADMINISTRATIF');

    // Create existing paid payment
    createPayment([
        'race_registration_id' => $registration->id,
        'user_id' => $staffUser->id,
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $useCase = new RecordManualPayment;

    expect(fn () => $useCase->execute($registration, $staffUser, 50.00))
        ->toThrow(\InvalidArgumentException::class);
});

// ============================================================================
// RaceRegistration Payment Relations Tests
// ============================================================================

test('race registration has payments relation', function () {
    $registration = RaceRegistration::factory()->create();

    createPayment([
        'race_registration_id' => $registration->id,
        'status' => 'pending',
    ]);

    createPayment([
        'race_registration_id' => $registration->id,
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    expect($registration->payments)->toHaveCount(2);
});

test('race registration detects paid status', function () {
    $registration = RaceRegistration::factory()->create();

    expect($registration->isPaid())->toBeFalse();

    createPayment([
        'race_registration_id' => $registration->id,
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $registration->refresh();
    expect($registration->isPaid())->toBeTrue();
});

test('race registration detects pending payment', function () {
    $registration = RaceRegistration::factory()->create();

    expect($registration->hasPendingPayment())->toBeFalse();

    createPayment([
        'race_registration_id' => $registration->id,
        'status' => 'pending',
    ]);

    $registration->refresh();
    expect($registration->hasPendingPayment())->toBeTrue();
});

test('race registration calculates total paid amount', function () {
    $registration = RaceRegistration::factory()->create();

    createPayment([
        'race_registration_id' => $registration->id,
        'status' => 'paid',
        'amount_cents' => 5000,
        'paid_at' => now(),
    ]);

    createPayment([
        'race_registration_id' => $registration->id,
        'status' => 'pending',
        'amount_cents' => 3000,
    ]);

    expect($registration->getPaidAmountCents())->toBe(5000);
});

// ============================================================================
// Payment Model Tests
// ============================================================================

test('payment model formats amount correctly', function () {
    $payment = createPayment([
        'amount' => 50.00,
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'status' => 'paid',
    ]);

    expect($payment->formatted_amount)->toContain('50,00');
});

test('payment model detects refundable stripe payment', function () {
    $stripePayment = createPayment([
        'method' => 'stripe',
        'status' => 'paid',
        'stripe_payment_intent_id' => 'pi_test_123',
        'paid_at' => now(),
    ]);

    expect($stripePayment->canBeRefunded())->toBeTrue();
});

test('payment model manual payments cannot be refunded', function () {
    $manualPayment = createPayment([
        'method' => 'manual',
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    expect($manualPayment->canBeRefunded())->toBeFalse();
});

test('payment model pending payments cannot be refunded', function () {
    $pendingPayment = createPayment([
        'method' => 'stripe',
        'status' => 'pending',
    ]);

    expect($pendingPayment->canBeRefunded())->toBeFalse();
});

// ============================================================================
// Payment Scopes Tests
// ============================================================================

test('payment pending scope filters correctly', function () {
    createPayment(['status' => 'pending']);
    createPayment(['status' => 'paid', 'paid_at' => now()]);
    createPayment(['status' => 'failed']);

    expect(Payment::pending()->count())->toBe(1);
});

test('payment paid scope filters correctly', function () {
    createPayment(['status' => 'pending']);
    createPayment(['status' => 'paid', 'paid_at' => now()]);
    createPayment(['status' => 'paid', 'paid_at' => now()]);

    expect(Payment::paid()->count())->toBe(2);
});

// ============================================================================
// StripePaymentService Tests
// ============================================================================

test('stripe payment service formats amount correctly', function () {
    $service = app(StripePaymentService::class);

    expect($service->formatAmount(5000))->toContain('50')
        ->and($service->formatAmount(12345))->toContain('123');
});

// ============================================================================
// Config Tests
// ============================================================================

test('stripe config has required structure', function () {
    expect(config('stripe.currency'))->toBeString()
        ->and(config('stripe.payment_methods'))->toBeArray();
});

test('stripe config has payment methods configured', function () {
    $methods = config('stripe.payment_methods');

    expect($methods)->toBeArray()
        ->and($methods)->toContain('card');
});
