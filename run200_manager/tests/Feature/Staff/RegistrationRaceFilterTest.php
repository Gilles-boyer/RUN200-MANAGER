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

test('staff can filter registrations by race with a single Livewire model binding', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);

    $november = Race::factory()->create(['name' => 'Course de novembre']);
    $other = Race::factory()->create(['name' => 'Autre course']);
    $novemberRegistration = RaceRegistration::factory()->for($november)->create();
    $otherRegistration = RaceRegistration::factory()->for($other)->create();
    $novemberRegistration->pilot->update(['last_name' => 'PiloteNovembreUnique']);
    $otherRegistration->pilot->update(['last_name' => 'PiloteAutreUnique']);

    $response = $this->get(route('staff.registrations.index'))->assertOk();
    preg_match('/<select\b[^>]*wire:model\.live="raceId"[^>]*>/', $response->getContent(), $select);
    expect($select[0] ?? null)->not->toBeNull()
        ->and($select[0])->not->toContain('wire:model="raceId"');

    Livewire::test(Index::class)
        ->set('raceId', $november->id)
        ->assertHasNoErrors()
        ->assertSee('PILOTENOVEMBREUNIQUE')
        ->assertDontSee('PILOTEAUTREUNIQUE');
});
