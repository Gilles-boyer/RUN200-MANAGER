<?php

use App\Application\Payments\UseCases\CreateStripeCheckout;
use App\Livewire\Pilot\Registrations\Payment as PaymentPage;
use App\Models\Car;
use App\Models\Payment;
use App\Models\Pilot;
use App\Models\RaceRegistration;
use App\Models\User;
use Livewire\Livewire;

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
        ->assertSee('Reprendre le paiement')
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
