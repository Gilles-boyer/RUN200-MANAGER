<?php

use App\Application\Payments\UseCases\CreateStripeCheckout;
use App\Infrastructure\Payments\Stripe\StripePaymentService;
use App\Livewire\Pilot\Registrations\Payment as PaymentPage;
use App\Models\Car;
use App\Models\Payment;
use App\Models\Pilot;
use App\Models\RaceRegistration;
use App\Models\User;
use Livewire\Livewire;
use Stripe\Checkout\Session;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);
    $this->user = User::factory()->create();
    $this->user->assignRole('PILOTE');
    $pilot = Pilot::factory()->create(['user_id' => $this->user->id]);
    $car = Car::factory()->create(['pilot_id' => $pilot->id]);
    $this->registration = RaceRegistration::factory()->create([
        'pilot_id' => $pilot->id,
        'car_id' => $car->id,
        'status' => 'ACCEPTED',
    ]);
    $this->actingAs($this->user);
});

function checkoutGuardPayment(RaceRegistration $registration, User $user, string $status): Payment
{
    return Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $user->id,
        'amount' => 50,
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'method' => 'stripe',
        'status' => $status,
        'stripe_session_id' => 'cs_test_'.$status.'_'.uniqid(),
        'paid_at' => $status === 'paid' ? now() : null,
        'metadata' => ['session_url' => 'https://checkout.stripe.com/test-session'],
    ]);
}

function mockCheckoutLookup(Payment $payment, array $overrides = []): StripePaymentService
{
    $data = array_merge([
        'id' => $payment->stripe_session_id,
        'status' => 'open',
        'payment_status' => 'unpaid',
        'amount_total' => $payment->amount_cents,
        'currency' => strtolower($payment->currency),
        'payment_intent' => null,
        'url' => 'https://checkout.stripe.com/fresh-session',
    ], $overrides);

    $stripe = Mockery::mock(StripePaymentService::class);
    $stripe->shouldReceive('retrieveSession')
        ->once()
        ->with($payment->stripe_session_id)
        ->andReturn(Session::constructFrom($data));
    app()->instance(StripePaymentService::class, $stripe);

    return $stripe;
}

test('a paid attempt blocks another Checkout session even when an older attempt is pending', function () {
    checkoutGuardPayment($this->registration, $this->user, 'pending');
    checkoutGuardPayment($this->registration, $this->user, 'paid');

    expect(fn () => app(CreateStripeCheckout::class)->execute($this->registration, $this->user))
        ->toThrow(InvalidArgumentException::class, 'déjà été payée');

    expect($this->registration->payments()->count())->toBe(2);
    Livewire::test(PaymentPage::class, ['registration' => $this->registration])
        ->assertSee('Inscription payée')
        ->assertDontSee('Créer un nouveau paiement');
});

test('an open Checkout session cannot be replaced while it may still accept payment', function () {
    checkoutGuardPayment($this->registration, $this->user, 'pending');

    expect(fn () => app(CreateStripeCheckout::class)->execute($this->registration, $this->user))
        ->toThrow(InvalidArgumentException::class, 'déjà en cours');

    Livewire::test(PaymentPage::class, ['registration' => $this->registration])
        ->assertSee('Vérifier et reprendre le paiement')
        ->assertDontSee('Créer un nouveau paiement');
});

test('the payment page offers the e-card only after registration acceptance', function () {
    checkoutGuardPayment($this->registration, $this->user, 'paid');
    $this->registration->update(['status' => 'PENDING_VALIDATION']);

    Livewire::test(PaymentPage::class, ['registration' => $this->registration])
        ->assertSee('Inscription payée')
        ->assertSee('après validation de votre inscription')
        ->assertDontSee('Voir ma e-carte');

    $this->registration->update(['status' => 'ACCEPTED']);
    Livewire::test(PaymentPage::class, ['registration' => $this->registration])
        ->assertSee('Voir ma e-carte');
});

test('resume checks Stripe and uses the current open Checkout URL', function () {
    $payment = checkoutGuardPayment($this->registration, $this->user, 'pending');
    mockCheckoutLookup($payment);

    Livewire::test(PaymentPage::class, ['registration' => $this->registration])
        ->call('resumePayment')
        ->assertRedirect('https://checkout.stripe.com/fresh-session');

    expect($payment->fresh()->status->value)->toBe('pending')
        ->and($this->registration->payments()->count())->toBe(1);
});

test('an expired unpaid Checkout is closed and the retry action appears immediately', function () {
    $payment = checkoutGuardPayment($this->registration, $this->user, 'pending');
    $stripe = mockCheckoutLookup($payment, ['status' => 'expired', 'url' => null]);

    Livewire::test(PaymentPage::class, ['registration' => $this->registration])
        ->call('resumePayment')
        ->assertSet('canRetryExpiredPayment', true)
        ->assertSee('Réessayer le paiement')
        ->assertDontSee('Vérifier et reprendre le paiement');

    expect($payment->fresh()->status->value)->toBe('cancelled')
        ->and($this->registration->payments()->count())->toBe(1);

    $stripe->shouldReceive('createCheckoutSession')->once()->andReturn(Session::constructFrom([
        'id' => 'cs_test_replacement',
        'url' => 'https://checkout.stripe.com/replacement',
        'expires_at' => now()->addDay()->timestamp,
    ]));
    $created = app(CreateStripeCheckout::class)->execute($this->registration, $this->user, 5000, 'EUR');
    expect($created['payment']->status->value)->toBe('pending')
        ->and($this->registration->payments()->count())->toBe(2);
    expect(fn () => app(CreateStripeCheckout::class)->execute($this->registration, $this->user, 5000, 'EUR'))
        ->toThrow(InvalidArgumentException::class, 'déjà en cours');
});

test('resume shows the pending attempt amount and retry shows the current race fee', function () {
    $this->registration->race->update(['entry_fee_cents' => 12000]);
    $payment = checkoutGuardPayment($this->registration, $this->user, 'pending');
    $payment->update(['amount' => '100.00', 'amount_cents' => 10000]);
    mockCheckoutLookup($payment, ['status' => 'expired', 'url' => null]);

    Livewire::test(PaymentPage::class, ['registration' => $this->registration])
        ->assertSee('100,00 EUR')
        ->assertDontSee('120,00 €')
        ->call('resumePayment')
        ->assertSee('Réessayer le paiement de 120,00 €')
        ->assertDontSee('100,00 EUR');

    expect($payment->fresh()->status->value)->toBe('cancelled');
});

test('a completed paid Checkout awaits historical reconciliation without a second payment', function () {
    $payment = checkoutGuardPayment($this->registration, $this->user, 'pending');
    mockCheckoutLookup($payment, [
        'status' => 'complete',
        'payment_status' => 'paid',
        'payment_intent' => 'pi_test_already_paid',
        'url' => null,
    ]);

    Livewire::test(PaymentPage::class, ['registration' => $this->registration])
        ->call('resumePayment')
        ->assertSee('Stripe indique que ce paiement a été effectué')
        ->assertSee('Ne relancez pas le paiement');

    expect($payment->fresh()->status->value)->toBe('pending')
        ->and($payment->fresh()->paid_at)->toBeNull()
        ->and($this->registration->payments()->count())->toBe(1);
    expect(fn () => app(CreateStripeCheckout::class)->execute($this->registration, $this->user, 5000, 'EUR'))
        ->toThrow(InvalidArgumentException::class, 'déjà en cours');
});

test('a mismatched or ambiguous Stripe session never closes the local attempt', function (array $overrides) {
    $payment = checkoutGuardPayment($this->registration, $this->user, 'pending');
    mockCheckoutLookup($payment, $overrides);

    Livewire::test(PaymentPage::class, ['registration' => $this->registration])
        ->call('resumePayment')
        ->assertSee('Nous vérifions votre paiement');

    expect($payment->fresh()->status->value)->toBe('pending')
        ->and($this->registration->payments()->count())->toBe(1);
})->with([
    'complete but unpaid' => [['status' => 'complete', 'payment_status' => 'unpaid']],
    'wrong amount' => [['status' => 'expired', 'amount_total' => 9999]],
    'wrong currency' => [['status' => 'expired', 'currency' => 'usd']],
    'expired with intent' => [['status' => 'expired', 'payment_intent' => 'pi_test_processing']],
]);

test('a Stripe lookup failure keeps the local payment pending', function () {
    $payment = checkoutGuardPayment($this->registration, $this->user, 'pending');
    $stripe = Mockery::mock(StripePaymentService::class);
    $stripe->shouldReceive('retrieveSession')->once()->andThrow(new RuntimeException('Stripe unavailable'));
    app()->instance(StripePaymentService::class, $stripe);

    Livewire::test(PaymentPage::class, ['registration' => $this->registration])
        ->call('resumePayment')
        ->assertSee('Impossible de vérifier ce paiement');

    expect($payment->fresh()->status->value)->toBe('pending');
});

test('a duplicated local Stripe session is never reconciled against the wrong payment', function () {
    $payment = checkoutGuardPayment($this->registration, $this->user, 'pending');
    $other = checkoutGuardPayment($this->registration, $this->user, 'pending');
    $other->update(['stripe_session_id' => $payment->stripe_session_id]);
    $stripe = Mockery::mock(StripePaymentService::class);
    $stripe->shouldNotReceive('retrieveSession');
    app()->instance(StripePaymentService::class, $stripe);

    Livewire::test(PaymentPage::class, ['registration' => $this->registration])
        ->call('resumePayment')
        ->assertSee('Nous vérifions votre paiement');

    expect($payment->fresh()->status->value)->toBe('pending')
        ->and($other->fresh()->status->value)->toBe('pending')
        ->and($this->registration->payments()->count())->toBe(2);
});

test('a paid Stripe session with a conflicting local intent remains blocked', function () {
    $payment = checkoutGuardPayment($this->registration, $this->user, 'pending');
    $payment->update(['stripe_payment_intent_id' => 'pi_test_other']);
    mockCheckoutLookup($payment, [
        'status' => 'complete',
        'payment_status' => 'paid',
        'payment_intent' => 'pi_test_paid',
        'url' => null,
    ]);

    Livewire::test(PaymentPage::class, ['registration' => $this->registration])
        ->call('resumePayment')
        ->assertSee('Nous vérifions votre paiement');

    expect($payment->fresh()->status->value)->toBe('pending')
        ->and($payment->fresh()->paid_at)->toBeNull();
});

test('an expired remote session does not cancel a local attempt with an intent to reconcile', function () {
    $payment = checkoutGuardPayment($this->registration, $this->user, 'pending');
    $payment->update(['stripe_payment_intent_id' => 'pi_test_local']);
    mockCheckoutLookup($payment, ['status' => 'expired', 'payment_intent' => null, 'url' => null]);

    Livewire::test(PaymentPage::class, ['registration' => $this->registration])
        ->call('resumePayment')
        ->assertSee('Nous vérifions votre paiement')
        ->assertDontSee('Réessayer le paiement');

    expect($payment->fresh()->status->value)->toBe('pending')
        ->and($payment->fresh()->stripe_payment_intent_id)->toBe('pi_test_local');
});

test('a payment confirmed between page display and resume shows success without a false error', function () {
    $payment = checkoutGuardPayment($this->registration, $this->user, 'pending');
    $component = Livewire::test(PaymentPage::class, ['registration' => $this->registration]);
    $payment->update(['status' => 'paid', 'paid_at' => now(), 'stripe_payment_intent_id' => 'pi_test_confirmed']);

    $component->call('resumePayment')
        ->assertSet('errorMessage', null)
        ->assertSee('Votre paiement est enregistré.')
        ->assertSee('Inscription payée');

    expect($payment->fresh()->status->value)->toBe('paid')
        ->and($this->registration->payments()->count())->toBe(1);
});

test('resume never opens an old Checkout when another payment was confirmed meanwhile', function () {
    checkoutGuardPayment($this->registration, $this->user, 'pending');
    $component = Livewire::test(PaymentPage::class, ['registration' => $this->registration]);
    checkoutGuardPayment($this->registration, $this->user, 'paid');
    $stripe = Mockery::mock(StripePaymentService::class);
    $stripe->shouldNotReceive('retrieveSession');
    app()->instance(StripePaymentService::class, $stripe);

    $component->call('resumePayment')
        ->assertSet('errorMessage', null)
        ->assertSee('Votre paiement est enregistré.')
        ->assertSee('Inscription payée');

    expect($this->registration->payments()->where('status', 'paid')->count())->toBe(1)
        ->and($this->registration->payments()->where('status', 'pending')->count())->toBe(1);
});

test('resume never redirects to an open Stripe session after the registration is refused', function () {
    $payment = checkoutGuardPayment($this->registration, $this->user, 'pending');
    $component = Livewire::test(PaymentPage::class, ['registration' => $this->registration]);
    $this->registration->update(['status' => 'REFUSED']);
    $stripe = Mockery::mock(StripePaymentService::class);
    $stripe->shouldNotReceive('retrieveSession');
    app()->instance(StripePaymentService::class, $stripe);

    $component->call('resumePayment')
        ->assertSee('Cette inscription ne peut plus être payée')
        ->assertDontSee('Réessayer le paiement');

    expect($payment->fresh()->status->value)->toBe('pending');
});

test('another pilot cannot resume a Checkout after authentication changes', function () {
    $payment = checkoutGuardPayment($this->registration, $this->user, 'pending');
    $component = Livewire::test(PaymentPage::class, ['registration' => $this->registration]);
    $otherUser = User::factory()->create();
    $otherUser->assignRole('PILOTE');
    Pilot::factory()->create(['user_id' => $otherUser->id]);
    $this->actingAs($otherUser);

    $component->call('resumePayment')->assertForbidden();

    expect($payment->fresh()->status->value)->toBe('pending');
});
