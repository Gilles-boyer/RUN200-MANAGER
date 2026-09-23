<?php

use App\Application\Registrations\UseCases\AssignPaddockSpot;
use App\Livewire\Pilot\Registrations\PaddockSelection;
use App\Livewire\Staff\Paddock\ManagePaddock;
use App\Models\PaddockSpot;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function paddockSpot(string $number, string $zone = 'A'): PaddockSpot
{
    return PaddockSpot::create(['spot_number' => $number, 'zone' => $zone, 'is_available' => true]);
}

test('a pilot can choose and change a spot after the administrative checkpoint', function () {
    $race = Race::factory()->open()->create();
    $registration = RaceRegistration::factory()->for($race)->create(['status' => 'ADMIN_CHECKED']);
    $first = paddockSpot('A1');
    $second = paddockSpot('A2');
    $pilotUser = $registration->pilot->user;
    $pilotUser->assignRole('PILOTE');
    $this->actingAs($pilotUser);

    Livewire::test(PaddockSelection::class, ['registration' => $registration])
        ->call('selectSpot', $first->id)
        ->call('confirmSelection')
        ->assertHasNoErrors();

    expect($registration->fresh()->paddock_spot_id)->toBe($first->id);

    Livewire::test(PaddockSelection::class, ['registration' => $registration->fresh()])
        ->call('selectSpot', $second->id)
        ->assertSee('Confirmer le changement')
        ->call('confirmSelection')
        ->assertHasNoErrors();

    expect($registration->fresh()->paddock_spot_id)->toBe($second->id)
        ->and($registration->fresh()->paddock)->toBe('A2');
});

test('staff can assign a free spot without forcing or displacing another pilot', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $race = Race::factory()->open()->create();
    $firstRegistration = RaceRegistration::factory()->for($race)->accepted()->create();
    $secondRegistration = RaceRegistration::factory()->for($race)->accepted()->create();
    $freeSpot = paddockSpot('A3');
    $this->actingAs($staff);

    Livewire::test(ManagePaddock::class)
        ->call('openAssignModal', $freeSpot->id)
        ->set('registrationToAssignId', $firstRegistration->id)
        ->call('assignSpotToRegistration')
        ->assertHasNoErrors();

    expect($firstRegistration->fresh()->paddock_spot_id)->toBe($freeSpot->id);

    Livewire::test(ManagePaddock::class)
        ->call('openAssignModal', $freeSpot->id)
        ->set('registrationToAssignId', $secondRegistration->id)
        ->call('assignSpotToRegistration')
        ->assertSee('déjà réservé');

    expect($firstRegistration->fresh()->paddock_spot_id)->toBe($freeSpot->id)
        ->and($secondRegistration->fresh()->paddock_spot_id)->toBeNull();

    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $this->actingAs($admin);
    Livewire::test(ManagePaddock::class)
        ->call('openAssignModal', $freeSpot->id)
        ->set('registrationToAssignId', $secondRegistration->id)
        ->call('assignSpotToRegistration')
        ->assertSee('déjà réservé');

    expect($firstRegistration->fresh()->paddock_spot_id)->toBe($freeSpot->id)
        ->and($secondRegistration->fresh()->paddock_spot_id)->toBeNull();
});

test('staff cannot assign a registration from another selected race', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $selectedRace = Race::factory()->open()->create();
    $otherRace = Race::factory()->closed()->create();
    $registration = RaceRegistration::factory()->for($otherRace)->accepted()->create();
    $spot = paddockSpot('A4');
    $this->actingAs($staff);

    Livewire::test(ManagePaddock::class)
        ->set('selectedRaceId', $selectedRace->id)
        ->call('openAssignModal', $spot->id)
        ->set('registrationToAssignId', $registration->id)
        ->call('assignSpotToRegistration')
        ->assertSee('course sélectionnée');

    expect($registration->fresh()->paddock_spot_id)->toBeNull();
});

test('staff paddock opens on the upcoming active race', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    Race::factory()->create(['status' => 'PUBLISHED', 'race_date' => now()->subMonth()]);
    $upcoming = Race::factory()->open()->create(['race_date' => now()->addMonth()]);
    $this->actingAs($staff);

    Livewire::test(ManagePaddock::class)
        ->assertSet('selectedRaceId', $upcoming->id);
});

test('a spot taken after pilot selection reports a clear error and keeps its owner', function () {
    $race = Race::factory()->open()->create();
    $pilotRegistration = RaceRegistration::factory()->for($race)->accepted()->create();
    $otherRegistration = RaceRegistration::factory()->for($race)->accepted()->create();
    $spot = paddockSpot('A5');
    $pilotUser = $pilotRegistration->pilot->user;
    $pilotUser->assignRole('PILOTE');
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $this->actingAs($pilotUser);

    $selection = Livewire::test(PaddockSelection::class, ['registration' => $pilotRegistration])
        ->call('selectSpot', $spot->id);

    (new AssignPaddockSpot)->execute($otherRegistration, $spot, $admin);

    $selection->call('confirmSelection')
        ->assertSee('déjà réservé');

    expect($otherRegistration->fresh()->paddock_spot_id)->toBe($spot->id)
        ->and($pilotRegistration->fresh()->paddock_spot_id)->toBeNull();
});

test('zone filters apply to the map and an occupied spot does not expose another pilot', function () {
    $race = Race::factory()->open()->create();
    $registration = RaceRegistration::factory()->for($race)->accepted()->create();
    $other = RaceRegistration::factory()->for($race)->accepted()->create();
    $freeSpot = paddockSpot('A6');
    $occupiedSpot = paddockSpot('B6', 'B');
    $freeSpot->update(['position_x' => 100, 'position_y' => 100]);
    $occupiedSpot->update(['position_x' => 200, 'position_y' => 100]);
    $pilotUser = $registration->pilot->user;
    $pilotUser->assignRole('PILOTE');
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    (new AssignPaddockSpot)->execute($other, $occupiedSpot, $admin);
    $this->actingAs($pilotUser);

    Livewire::test(PaddockSelection::class, ['registration' => $registration])
        ->call('setViewMode', 'map')
        ->call('filterByZone', 'A')
        ->assertSee('A6')
        ->assertDontSee('B6')
        ->call('filterByZone', null)
        ->call('selectSpot', $occupiedSpot->id)
        ->assertSee('Emplacement réservé pour cette course')
        ->assertDontSee($other->pilot->last_name);
});
