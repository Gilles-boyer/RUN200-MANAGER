<?php

use App\Livewire\Staff\Registrations\EngagementSign;
use App\Livewire\Staff\Scan\Scanner;
use App\Models\CarTechInspectionHistory;
use App\Models\Checkpoint;
use App\Models\CheckpointPassage;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\TechInspection;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Checkpoint::factory()->adminCheck()->create();
    Checkpoint::factory()->techCheck()->create();
    Mail::fake();
});

test('signing an engagement completes the admin checkpoint and makes the pilot ready for technical control', function () {
    $admin = User::factory()->create();
    $admin->assignRole('STAFF_ADMINISTRATIF');
    $race = Race::factory()->open()->create();
    $registration = RaceRegistration::factory()->accepted()->for($race)->create();
    $this->actingAs($admin);

    Livewire::test(EngagementSign::class, ['registration' => $registration])
        ->set('signatureData', 'data:image/png;base64,test')
        ->call('submitEngagement')
        ->assertHasNoErrors();

    expect($registration->fresh()->status)->toBe('ADMIN_CHECKED')
        ->and($registration->hasPassedCheckpoint('ADMIN_CHECK'))->toBeTrue()
        ->and($registration->engagementForm?->admin_validated_by)->toBe($admin->id)
        ->and($registration->engagementForm?->admin_validated_at)->not->toBeNull();

    $passage = $registration->getPassageForCheckpoint('ADMIN_CHECK');
    expect($passage->meta['source'])->toBe('engagement_signature');

    $tech = User::factory()->create();
    $tech->assignRole('CONTROLEUR_TECHNIQUE');
    $this->actingAs($tech);

    Livewire::test(Scanner::class, ['checkpointCode' => 'TECH_CHECK'])
        ->assertSet('scanMode', 'list')
        ->assertSee($registration->pilot->last_name)
        ->assertSee('Valider contrôle technique')
        ->call('setScanMode', 'camera')
        ->assertSee('Démarrer le scan');
});

test('technical list button records the inspection, checkpoint, and vehicle history once', function () {
    $race = Race::factory()->open()->create();
    $registration = RaceRegistration::factory()->accepted()->for($race)->create();
    $admin = User::factory()->create();
    $admin->assignRole('STAFF_ADMINISTRATIF');
    $this->actingAs($admin);
    Livewire::test(EngagementSign::class, ['registration' => $registration])
        ->set('signatureData', 'data:image/png;base64,test')
        ->call('submitEngagement');

    $tech = User::factory()->create();
    $tech->assignRole('CONTROLEUR_TECHNIQUE');
    $this->actingAs($tech);

    Livewire::test(Scanner::class, ['checkpointCode' => 'TECH_CHECK'])
        ->call('validateTechnicalCheck', $registration->id)
        ->assertSet('showSuccess', true)
        ->assertSee('Contrôle technique validé');

    expect($registration->fresh()->status)->toBe('TECH_CHECKED_OK')
        ->and($registration->hasPassedCheckpoint('TECH_CHECK'))->toBeTrue()
        ->and(TechInspection::where('race_registration_id', $registration->id)->count())->toBe(1)
        ->and(CarTechInspectionHistory::where('race_registration_id', $registration->id)->count())->toBe(1)
        ->and($registration->engagementForm->fresh()->tech_checked_at)->not->toBeNull()
        ->and($registration->getPassageForCheckpoint('TECH_CHECK')->meta['source'])->toBe('tech_list');

    Livewire::test(Scanner::class, ['checkpointCode' => 'TECH_CHECK'])
        ->call('validateTechnicalCheck', $registration->id)
        ->assertSet('showSuccess', false)
        ->assertSee('Ce contrôle technique est déjà enregistré');

    expect(CheckpointPassage::where('race_registration_id', $registration->id)->count())->toBe(2);
});

test('technical list button refuses registrations from another race', function () {
    $selectedRace = Race::factory()->open()->create();
    $otherRace = Race::factory()->closed()->create();
    $registration = RaceRegistration::factory()->accepted()->for($otherRace)->create();
    $tech = User::factory()->create();
    $tech->assignRole('CONTROLEUR_TECHNIQUE');
    $this->actingAs($tech);

    Livewire::test(Scanner::class, ['checkpointCode' => 'TECH_CHECK'])
        ->assertSet('selectedRaceId', $selectedRace->id)
        ->call('validateTechnicalCheck', $registration->id)
        ->assertSee('ne fait pas partie de la course sélectionnée');

    expect($registration->fresh()->status)->toBe('ACCEPTED')
        ->and(TechInspection::where('race_registration_id', $registration->id)->count())->toBe(0);
});
