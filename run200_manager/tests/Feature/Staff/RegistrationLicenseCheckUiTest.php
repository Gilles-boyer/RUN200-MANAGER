<?php

use App\Livewire\Staff\Registrations\Index;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('administrative staff can see the licence state and record an observed RCCR check', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);

    $race = Race::factory()->create([
        'race_date' => '2026-11-08',
        'required_license_code' => 'RCCR',
    ]);
    $registration = RaceRegistration::factory()->for($race)->create();
    $registration->pilot->update(['last_name' => 'PiloteVerifieUnique']);
    $unchecked = RaceRegistration::factory()->for($race)->create();
    $unchecked->pilot->update(['last_name' => 'PiloteAControlerUnique']);

    Livewire::test(Index::class)
        ->set('raceId', $race->id)
        ->assertSee('Licence à contrôler')
        ->call('openLicenseModal', $registration->id)
        ->assertSee('Code type observé')
        ->assertSee('Ouvrir la recherche FFSA')
        ->assertSee('wire:model.live="licenseValidUntil"', false)
        ->assertSee('wire:model.live="licenseReasonCode"', false)
        ->set('licenseResult', 'valid')
        ->call('confirmLicenseCheck')
        ->assertHasErrors(['licenseResult'])
        ->set('licenseSource', 'ligue_ffsa')
        ->set('licenseSourceAccessStatus', 'consulted')
        ->set('licenseTypeObserved', 'RCCR')
        ->set('licenseStateObserved', 'active')
        ->set('licenseYearObserved', '2026')
        ->set('licenseIdentityMatches', 'yes')
        ->set('licenseNumberMatches', 'yes')
        ->set('licenseValidForRaceDate', 'yes')
        ->set('licenseValidUntil', '2026-12-31')
        ->call('confirmLicenseCheck')
        ->assertHasNoErrors()
        ->assertDispatched('license-modal-closed')
        ->assertSee('Licence RCCR vérifiée')
        ->set('licenseFilter', 'valid')
        ->assertSee('PILOTEVERIFIEUNIQUE')
        ->assertDontSee('PILOTEACONTROLERUNIQUE')
        ->set('licenseFilter', 'not_checked')
        ->assertSee('PILOTEACONTROLERUNIQUE')
        ->assertDontSee('PILOTEVERIFIEUNIQUE');

    expect($registration->fresh()->licenseCheck->result)->toBe('valid');
});

test('entry staff cannot open or save a licence check', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ENTREE');
    $this->actingAs($staff);
    $race = Race::factory()->create(['required_license_code' => 'RCCR']);
    $registration = RaceRegistration::factory()->for($race)->create();

    Livewire::test(Index::class)->call('openLicenseModal', $registration->id)->assertForbidden();
    Livewire::test(Index::class)->call('confirmLicenseCheck')->assertForbidden();
});

test('a red alert cannot be recorded from an active licence or an unconfirmed observation', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $race = Race::factory()->create(['race_date' => '2026-11-08', 'required_license_code' => 'RCCR']);
    $registration = RaceRegistration::factory()->for($race)->create();

    $component = Livewire::test(Index::class)
        ->call('openLicenseModal', $registration->id)
        ->set('licenseResult', 'invalid')
        ->set('licenseSourceAccessStatus', 'consulted')
        ->set('licenseTypeObserved', 'RCCR')
        ->set('licenseYearObserved', '2026')
        ->set('licenseIdentityMatches', 'yes')
        ->set('licenseNumberMatches', 'yes')
        ->set('licenseValidForRaceDate', 'no')
        ->set('licenseStateObserved', 'active')
        ->set('licenseReasonCode', 'expired')
        ->set('licenseValidUntil', '2026-12-31')
        ->call('confirmLicenseCheck')
        ->assertHasErrors(['licenseResult']);

    $component->set('licenseStateObserved', 'expired')
        ->set('licenseReasonCode', '')
        ->set('licenseValidUntil', '2026-10-31');
    preg_match('/<button\b[^>]*wire:click="confirmLicenseCheck"[^>]*>/s', $component->html(), $saveButton);
    expect(preg_match('/\sdisabled(?:\s|=|>)/', $saveButton[0] ?? ''))->toBe(1);

    $component->set('licenseReasonCode', 'expired');
    preg_match('/<button\b[^>]*wire:click="confirmLicenseCheck"[^>]*>/s', $component->html(), $saveButton);
    expect(preg_match('/\sdisabled(?:\s|=|>)/', $saveButton[0] ?? ''))->toBe(0);

    $component->set('licenseValidUntil', '');
    preg_match('/<button\b[^>]*wire:click="confirmLicenseCheck"[^>]*>/s', $component->html(), $saveButton);
    expect(preg_match('/\sdisabled(?:\s|=|>)/', $saveButton[0] ?? ''))->toBe(1);
    $component->set('licenseValidUntil', '2026-10-31');

    $component
        ->call('confirmLicenseCheck')
        ->assertHasNoErrors();

    expect($registration->fresh()->licenseCheck->result)->toBe('invalid');
});
