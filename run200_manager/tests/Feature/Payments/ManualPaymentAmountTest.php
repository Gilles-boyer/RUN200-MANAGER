<?php

use App\Livewire\Staff\Registrations\PaymentManager;
use App\Models\Payment;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\User;
use Livewire\Livewire;

test('manual payment starts with the race fee and records an entered euro amount exactly', function () {
    $user = User::factory()->create();
    $race = Race::factory()->create(['entry_fee_cents' => 7350]);
    $registration = RaceRegistration::factory()->accepted()->for($race)->create();
    $this->actingAs($user);

    Livewire::test(PaymentManager::class, ['registration' => $registration])
        ->call('openManualPaymentModal')
        ->assertSet('manualAmount', '73,50')
        ->assertSee('Montant en euros')
        ->set('manualAmount', '50,75')
        ->call('recordManualPayment')
        ->assertHasNoErrors();

    $payment = Payment::where('race_registration_id', $registration->id)->sole();
    expect($payment->amount)->toBe('50.75')
        ->and($payment->amount_cents)->toBe(5075);
});

test('manual payment rejects fractional cents and amounts below one euro', function () {
    $user = User::factory()->create();
    $registration = RaceRegistration::factory()->accepted()->create();
    $this->actingAs($user);

    Livewire::test(PaymentManager::class, ['registration' => $registration])
        ->call('openManualPaymentModal')
        ->set('manualAmount', '50,755')
        ->call('recordManualPayment')
        ->assertHasErrors(['manualAmount']);

    Livewire::test(PaymentManager::class, ['registration' => $registration])
        ->call('openManualPaymentModal')
        ->set('manualAmount', '0,50')
        ->call('recordManualPayment')
        ->assertHasErrors(['manualAmount']);

    expect(Payment::where('race_registration_id', $registration->id)->count())->toBe(0);
});
