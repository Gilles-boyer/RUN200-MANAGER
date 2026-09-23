<?php

use App\Models\Car;
use App\Models\Payment;
use App\Models\Pilot;
use App\Models\RaceRegistration;
use App\Models\User;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);

    $this->user = User::factory()->create();
    $this->user->assignRole('PILOTE');
    $pilot = Pilot::factory()->create(['user_id' => $this->user->id]);
    $car = Car::factory()->create(['pilot_id' => $pilot->id]);
    $this->registration = RaceRegistration::factory()->create([
        'pilot_id' => $pilot->id,
        'car_id' => $car->id,
        'status' => 'PENDING_PAYMENT',
    ]);
    $this->payment = Payment::create([
        'race_registration_id' => $this->registration->id,
        'user_id' => $this->user->id,
        'amount' => 50,
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'method' => 'stripe',
        'status' => 'pending',
        'stripe_session_id' => 'cs_test_success_123',
    ]);
    $this->actingAs($this->user);
});

function stripeReturnUrl(RaceRegistration $registration, ?string $sessionId = 'cs_test_success_123'): string
{
    $url = route('pilot.registrations.payment.success', $registration);

    return $sessionId === null ? $url : $url.'?session_id='.urlencode($sessionId);
}

test('return page waits for a server confirmed Stripe payment', function () {
    $this->get(stripeReturnUrl($this->registration))
        ->assertOk()
        ->assertSee('Vérification du paiement en cours')
        ->assertDontSee('Paiement confirmé')
        ->assertDontSee('Voir ma e-carte');

    $this->payment->update(['status' => 'processing']);
    $this->get(stripeReturnUrl($this->registration))
        ->assertOk()
        ->assertSee('Vérification du paiement en cours');
});

test('paid return page waits for registration acceptance before showing the e-card', function () {
    $this->payment->update(['status' => 'paid', 'paid_at' => now()]);
    $this->registration->update(['status' => 'PENDING_VALIDATION']);

    $this->get(stripeReturnUrl($this->registration))
        ->assertOk()
        ->assertSee('Paiement confirmé')
        ->assertSee('après validation de votre inscription')
        ->assertDontSee('Voir ma e-carte');

    $this->registration->update(['status' => 'ACCEPTED']);
    $this->get(stripeReturnUrl($this->registration))
        ->assertOk()
        ->assertSee('Paiement confirmé')
        ->assertSee('Voir ma e-carte');
});

test('failed and unknown payment sessions never appear as paid', function () {
    $this->payment->update(['status' => 'failed']);
    $this->get(stripeReturnUrl($this->registration))
        ->assertOk()
        ->assertSee('Paiement non confirmé')
        ->assertSee('Voir les options de paiement')
        ->assertDontSee('Voir ma e-carte');

    $this->payment->update(['status' => 'paid']);
    $this->get(stripeReturnUrl($this->registration, null))
        ->assertOk()
        ->assertSee('Paiement impossible à vérifier')
        ->assertDontSee('Paiement confirmé');
    $this->get(stripeReturnUrl($this->registration, 'cs_test_unknown'))
        ->assertOk()
        ->assertSee('Paiement impossible à vérifier');
});

test('a different pilot cannot inspect the Stripe return page', function () {
    $other = User::factory()->create();
    $other->assignRole('PILOTE');
    Pilot::factory()->create(['user_id' => $other->id]);

    $this->actingAs($other)
        ->get(stripeReturnUrl($this->registration))
        ->assertForbidden();
});

test('an incomplete profile can still access an existing registration and payment return', function () {
    $pilot = $this->user->pilot;
    $pilot->update(['photo_path' => null]);
    expect($pilot->fresh()->canRegisterForRace())->toBeFalse();

    $this->get(route('pilot.registrations.index'))->assertOk();
    $this->get(route('pilot.registrations.payment', $this->registration))->assertOk();
    $this->get(stripeReturnUrl($this->registration))->assertOk();

    $this->get(route('pilot.registrations.create', $this->registration->race))
        ->assertRedirect(route('pilot.profile.edit'));
});
