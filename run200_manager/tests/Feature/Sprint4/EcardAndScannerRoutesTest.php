<?php

declare(strict_types=1);

namespace Tests\Feature\Sprint4;

use App\Models\Checkpoint;
use App\Models\Pilot;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        'checkpoint.scan.admin_check',
        'checkpoint.scan.tech_check',
        'checkpoint.scan.entry',
        'checkpoint.scan.bracelet',
    ];
    foreach ($permissions as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    // Assign permissions to roles
    Role::findByName('STAFF_ADMINISTRATIF')->givePermissionTo('checkpoint.scan.admin_check');
    Role::findByName('CONTROLEUR_TECHNIQUE')->givePermissionTo('checkpoint.scan.tech_check');
    Role::findByName('STAFF_ENTREE')->givePermissionTo(['checkpoint.scan.entry', 'checkpoint.scan.bracelet']);
    Role::findByName('ADMIN')->givePermissionTo($permissions);

    // Create checkpoints
    Checkpoint::factory()->adminCheck()->create();
    Checkpoint::factory()->techCheck()->create();
    Checkpoint::factory()->entry()->create();
    Checkpoint::factory()->bracelet()->create();
});

// ============================================================================
// E-carte Route Tests
// ============================================================================

test('pilot cannot access e-carte of another pilot', function () {
    $pilotUser1 = User::factory()->create();
    $pilotUser1->assignRole('PILOTE');

    // Create complete pilot profile for user1 to pass middleware check
    $pilot1 = Pilot::factory()->create([
        'user_id' => $pilotUser1->id,
        'city' => 'Paris',
        'postal_code' => '75001',
        'photo_path' => 'pilots/photo.jpg',
        'emergency_contact_name' => 'Contact Emergency',
        'emergency_contact_phone' => '0601020304',
    ]);

    // Create a car for pilot1 to pass the middleware check
    \App\Models\Car::factory()->create(['pilot_id' => $pilot1->id]);

    $pilotUser2 = User::factory()->create();
    $pilotUser2->assignRole('PILOTE');

    $pilot2 = Pilot::factory()->create([
        'user_id' => $pilotUser2->id,
    ]);

    $registration = RaceRegistration::factory()->accepted()->create([
        'pilot_id' => $pilot2->id,
    ]);

    $response = $this->actingAs($pilotUser1)
        ->get(route('pilot.registrations.ecard', $registration));

    $response->assertStatus(403);
});

test('guest cannot access e-carte', function () {
    $pilotUser = User::factory()->create();
    $pilot = Pilot::factory()->create([
        'user_id' => $pilotUser->id,
    ]);
    $registration = RaceRegistration::factory()->accepted()->create([
        'pilot_id' => $pilot->id,
    ]);

    $response = $this->get(route('pilot.registrations.ecard', $registration));

    $response->assertRedirect(route('login'));
});

// ============================================================================
// Scanner Route Tests
// ============================================================================

test('staff without permission cannot access admin scanner', function () {
    $staff = User::factory()->create();
    $staff->assignRole('PILOTE');

    $response = $this->actingAs($staff)
        ->get(route('staff.scan.admin'));

    $response->assertStatus(403);
});

test('tech controller cannot access entry scanner', function () {
    $techController = User::factory()->create();
    $techController->assignRole('CONTROLEUR_TECHNIQUE');

    $response = $this->actingAs($techController)
        ->get(route('staff.scan.entry'));

    $response->assertStatus(403);
});

test('invalid checkpoint type returns 404', function () {
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');

    $response = $this->actingAs($admin)
        ->get('/staff/scan/invalid');

    $response->assertStatus(404);
});

test('guest cannot access scanner', function () {
    $response = $this->get(route('staff.scan.admin'));

    $response->assertRedirect(route('login'));
});
