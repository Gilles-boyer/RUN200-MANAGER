<?php

declare(strict_types=1);

namespace Tests\Feature\Sprint5;

use App\Models\RaceRegistration;
use App\Models\TechInspection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    $roles = ['ADMIN', 'PILOTE', 'STAFF_ADMINISTRATIF', 'CONTROLEUR_TECHNIQUE', 'STAFF_ENTREE', 'STAFF_SONO'];
    foreach ($roles as $role) {
        Role::findOrCreate($role, 'web');
    }

    // Create permissions
    $permissions = [
        'tech_inspection.manage',
        'tech_inspection.view',
    ];
    foreach ($permissions as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    Role::findByName('CONTROLEUR_TECHNIQUE')->givePermissionTo(['tech_inspection.manage', 'tech_inspection.view']);
    Role::findByName('ADMIN')->givePermissionTo($permissions);

    $this->techController = User::factory()->create();
    $this->techController->assignRole('CONTROLEUR_TECHNIQUE');

    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('ADMIN');

    $this->pilotUser = User::factory()->create();
    $this->pilotUser->assignRole('PILOTE');
});

// ============================================================================
// Route Access Tests
// ============================================================================

test('tech controller can access tech inspection page', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $response = $this->actingAs($this->techController)
        ->get(route('staff.registrations.tech', $registration));

    expect($response->status())->toBe(200);
});

test('admin can access tech inspection page', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $response = $this->actingAs($this->adminUser)
        ->get(route('staff.registrations.tech', $registration));

    expect($response->status())->toBe(200);
});

test('pilot cannot access tech inspection page', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $response = $this->actingAs($this->pilotUser)
        ->get(route('staff.registrations.tech', $registration));

    expect($response->status())->toBe(403);
});

test('staff administratif without permission cannot access tech inspection', function () {
    $staffAdmin = User::factory()->create();
    $staffAdmin->assignRole('STAFF_ADMINISTRATIF');

    $registration = RaceRegistration::factory()->accepted()->create();

    $response = $this->actingAs($staffAdmin)
        ->get(route('staff.registrations.tech', $registration));

    expect($response->status())->toBe(403);
});

test('guest cannot access tech inspection page', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $response = $this->get(route('staff.registrations.tech', $registration));

    expect($response->status())->toBe(302); // Redirect to login
});

// ============================================================================
// Livewire Component Tests
// ============================================================================

test('tech inspection form displays registration info', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    Livewire::actingAs($this->techController)
        ->test(\App\Livewire\Staff\Registrations\TechInspectionForm::class, ['registration' => $registration])
        ->assertSee($registration->pilot->last_name)
        ->assertSee($registration->pilot->first_name)
        ->assertSee($registration->car->race_number)
        ->assertSee($registration->car->make);
});

test('can submit OK inspection via livewire', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    Livewire::actingAs($this->techController)
        ->test(\App\Livewire\Staff\Registrations\TechInspectionForm::class, ['registration' => $registration])
        ->set('status', 'OK')
        ->call('confirmInspection')
        ->call('submitInspection')
        ->assertSee('Contrôle technique validé avec succès');

    $registration->refresh();
    expect($registration->status)->toBe('TECH_CHECKED_OK')
        ->and($registration->techInspection)->not->toBeNull();
});

test('can submit FAIL inspection with notes via livewire', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    Livewire::actingAs($this->techController)
        ->test(\App\Livewire\Staff\Registrations\TechInspectionForm::class, ['registration' => $registration])
        ->set('status', 'FAIL')
        ->set('notes', 'Pneus trop usés')
        ->call('confirmInspection')
        ->call('submitInspection')
        ->assertSee('Contrôle technique échoué enregistré');

    $registration->refresh();
    expect($registration->status)->toBe('TECH_CHECKED_FAIL')
        ->and($registration->techInspection->notes)->toBe('Pneus trop usés');
});

test('cannot submit FAIL inspection without notes via livewire', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    Livewire::actingAs($this->techController)
        ->test(\App\Livewire\Staff\Registrations\TechInspectionForm::class, ['registration' => $registration])
        ->set('status', 'FAIL')
        ->set('notes', '')
        ->call('confirmInspection')
        ->assertSee('Les notes sont obligatoires');
});

test('displays existing inspection result', function () {
    $registration = RaceRegistration::factory()->create(['status' => 'TECH_CHECKED_OK']);

    TechInspection::factory()
        ->forRegistration($registration)
        ->passed()
        ->inspectedBy($this->techController)
        ->create();

    Livewire::actingAs($this->techController)
        ->test(\App\Livewire\Staff\Registrations\TechInspectionForm::class, ['registration' => $registration])
        ->assertSee('Résultat du contrôle technique')
        ->assertSee('VALIDÉ');
});

test('can reset failed inspection via livewire', function () {
    $registration = RaceRegistration::factory()->create(['status' => 'TECH_CHECKED_FAIL']);

    TechInspection::factory()
        ->forRegistration($registration)
        ->failed('Problème identifié')
        ->inspectedBy($this->techController)
        ->create();

    Livewire::actingAs($this->techController)
        ->test(\App\Livewire\Staff\Registrations\TechInspectionForm::class, ['registration' => $registration])
        ->call('resetInspection')
        ->assertSee('Contrôle technique réinitialisé');

    $registration->refresh();
    expect($registration->status)->toBe('ACCEPTED')
        ->and($registration->techInspection)->toBeNull();
});

test('shows warning for ineligible registration', function () {
    $registration = RaceRegistration::factory()->pending()->create();

    Livewire::actingAs($this->techController)
        ->test(\App\Livewire\Staff\Registrations\TechInspectionForm::class, ['registration' => $registration])
        ->assertSee('pas éligible');
});

test('confirmation modal works correctly', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    Livewire::actingAs($this->techController)
        ->test(\App\Livewire\Staff\Registrations\TechInspectionForm::class, ['registration' => $registration])
        ->set('status', 'OK')
        ->call('confirmInspection')
        ->assertSet('showConfirmation', true)
        ->call('cancelConfirmation')
        ->assertSet('showConfirmation', false);
});
