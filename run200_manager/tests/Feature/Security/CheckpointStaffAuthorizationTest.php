<?php

use App\Livewire\Staff\Registrations\CheckpointsManager;
use App\Models\Checkpoint;
use App\Models\CheckpointPassage;
use App\Models\RaceRegistration;
use App\Models\TechInspection;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('entry staff cannot manually validate a technical checkpoint', function () {
    $entryStaff = User::factory()->create();
    $entryStaff->assignRole('STAFF_ENTREE');
    $this->actingAs($entryStaff);
    $registration = RaceRegistration::factory()->accepted()->create();
    $tech = Checkpoint::factory()->techCheck()->create();

    Livewire::test(CheckpointsManager::class, ['registration' => $registration])
        ->set('selectedCheckpointId', $tech->id)
        ->set('passageDate', now()->format('Y-m-d'))
        ->set('passageTime', now()->format('H:i'))
        ->call('savePassage')
        ->assertForbidden();

    expect($registration->fresh()->status)->toBe('ACCEPTED')
        ->and(TechInspection::where('race_registration_id', $registration->id)->exists())->toBeFalse();
});

test('manual checkpoint action requires the earlier checkpoint', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->accepted()->create();
    Checkpoint::factory()->adminCheck()->create();
    $tech = Checkpoint::factory()->techCheck()->create();

    Livewire::test(CheckpointsManager::class, ['registration' => $registration])
        ->set('selectedCheckpointId', $tech->id)
        ->set('passageDate', now()->format('Y-m-d'))
        ->set('passageTime', now()->format('H:i'))
        ->call('savePassage')
        ->assertHasErrors(['selectedCheckpointId']);

    expect($registration->passages()->count())->toBe(0);
});

test('manual administrative checkpoint updates the registration status and deletion restores it', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->accepted()->create();
    $adminCheck = Checkpoint::factory()->adminCheck()->create();

    Livewire::test(CheckpointsManager::class, ['registration' => $registration])
        ->set('selectedCheckpointId', $adminCheck->id)
        ->set('passageDate', now()->format('Y-m-d'))
        ->set('passageTime', now()->format('H:i'))
        ->call('savePassage')
        ->assertHasNoErrors();

    expect($registration->fresh()->status)->toBe('ADMIN_CHECKED');
    $passage = $registration->passages()->sole();

    Livewire::test(CheckpointsManager::class, ['registration' => $registration->fresh()])
        ->call('confirmDeletePassage', $passage->id)
        ->call('deletePassage')
        ->assertHasNoErrors();

    expect($registration->fresh()->status)->toBe('ACCEPTED')
        ->and($registration->passages()->count())->toBe(0);
});

test('manual deletion cannot remove a prerequisite while a later checkpoint remains', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $registration = RaceRegistration::factory()->create(['status' => 'TECH_CHECKED_OK']);
    $adminCheck = Checkpoint::factory()->adminCheck()->create();
    $techCheck = Checkpoint::factory()->techCheck()->create();
    $adminPassage = CheckpointPassage::factory()->create([
        'race_registration_id' => $registration->id,
        'checkpoint_id' => $adminCheck->id,
    ]);
    CheckpointPassage::factory()->create([
        'race_registration_id' => $registration->id,
        'checkpoint_id' => $techCheck->id,
    ]);

    Livewire::test(CheckpointsManager::class, ['registration' => $registration])
        ->call('confirmDeletePassage', $adminPassage->id)
        ->call('deletePassage')
        ->assertHasErrors(['passageToDelete']);

    expect($adminPassage->fresh())->not->toBeNull();
});

test('manual edit and deletion cannot target another registration passage', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($staff);
    $own = RaceRegistration::factory()->accepted()->create();
    $other = RaceRegistration::factory()->accepted()->create();
    $checkpoint = Checkpoint::factory()->adminCheck()->create();
    $foreignPassage = CheckpointPassage::factory()->create([
        'race_registration_id' => $other->id,
        'checkpoint_id' => $checkpoint->id,
    ]);

    Livewire::test(CheckpointsManager::class, ['registration' => $own])
        ->call('openEditPassageModal', $foreignPassage->id)
        ->assertNotFound();

    Livewire::test(CheckpointsManager::class, ['registration' => $own])
        ->call('confirmDeletePassage', $foreignPassage->id)
        ->assertNotFound();

    Livewire::test(CheckpointsManager::class, ['registration' => $own])
        ->set('editingPassageId', $foreignPassage->id)
        ->set('selectedCheckpointId', $checkpoint->id)
        ->set('passageDate', now()->format('Y-m-d'))
        ->set('passageTime', now()->format('H:i'))
        ->call('savePassage')
        ->assertNotFound();

    Livewire::test(CheckpointsManager::class, ['registration' => $own])
        ->set('passageToDelete', $foreignPassage)
        ->call('deletePassage')
        ->assertNotFound();

    expect($foreignPassage->fresh())->not->toBeNull();
});
