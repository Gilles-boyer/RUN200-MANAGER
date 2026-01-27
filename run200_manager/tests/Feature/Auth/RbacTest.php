<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

test('roles are created correctly', function () {
    expect(Role::count())->toBe(6);

    expect(Role::where('name', 'PILOTE')->exists())->toBeTrue();
    expect(Role::where('name', 'STAFF_ADMINISTRATIF')->exists())->toBeTrue();
    expect(Role::where('name', 'CONTROLEUR_TECHNIQUE')->exists())->toBeTrue();
    expect(Role::where('name', 'STAFF_ENTREE')->exists())->toBeTrue();
    expect(Role::where('name', 'STAFF_SONO')->exists())->toBeTrue();
    expect(Role::where('name', 'ADMIN')->exists())->toBeTrue();
});

test('pilot role has correct permissions', function () {
    $pilotRole = Role::findByName('PILOTE');

    expect($pilotRole->hasPermissionTo('pilot.manage_own_profile'))->toBeTrue();
    expect($pilotRole->hasPermissionTo('car.manage_own'))->toBeTrue();
    expect($pilotRole->hasPermissionTo('race.view_open'))->toBeTrue();
    expect($pilotRole->hasPermissionTo('race_registration.create'))->toBeTrue();

    // Should not have admin permissions
    expect($pilotRole->hasPermissionTo('admin.access'))->toBeFalse();
    expect($pilotRole->hasPermissionTo('race.manage'))->toBeFalse();
});

test('admin role has all permissions', function () {
    $adminRole = Role::findByName('ADMIN');

    // Admin should have all permissions
    $allPermissions = \Spatie\Permission\Models\Permission::all();

    foreach ($allPermissions as $permission) {
        expect($adminRole->hasPermissionTo($permission))->toBeTrue();
    }
});

test('staff administratif has validation permissions', function () {
    $staffRole = Role::findByName('STAFF_ADMINISTRATIF');

    expect($staffRole->hasPermissionTo('race_registration.validate'))->toBeTrue();
    expect($staffRole->hasPermissionTo('race_registration.assign_paddock'))->toBeTrue();
    expect($staffRole->hasPermissionTo('checkpoint.scan.admin_check'))->toBeTrue();

    // Should not have tech permissions
    expect($staffRole->hasPermissionTo('checkpoint.scan.tech_check'))->toBeFalse();
});

test('technical controller has tech permissions', function () {
    $techRole = Role::findByName('CONTROLEUR_TECHNIQUE');

    expect($techRole->hasPermissionTo('checkpoint.scan.tech_check'))->toBeTrue();
    expect($techRole->hasPermissionTo('tech_inspection.manage'))->toBeTrue();

    // Should not have admin validation permissions
    expect($techRole->hasPermissionTo('race_registration.validate'))->toBeFalse();
});

test('user can be assigned a role', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    expect($user->hasRole('PILOTE'))->toBeTrue();
    expect($user->isPilot())->toBeTrue();
    expect($user->isAdmin())->toBeFalse();
});

test('user can have multiple roles', function () {
    $user = User::factory()->create();
    $user->assignRole(['PILOTE', 'STAFF_ADMINISTRATIF']);

    expect($user->hasRole('PILOTE'))->toBeTrue();
    expect($user->hasRole('STAFF_ADMINISTRATIF'))->toBeTrue();
    expect($user->isPilot())->toBeTrue();
    expect($user->isStaff())->toBeTrue();
});

test('pilot cannot access admin routes', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $response = $this->actingAs($user)->get('/admin/home');

    expect($response->status())->toBe(403);
});

test('admin can access admin routes', function () {
    $user = User::factory()->create();
    $user->assignRole('ADMIN');

    $response = $this->actingAs($user)->get('/admin/home');

    expect($response->status())->toBe(200);
});

test('pilot can access pilot routes', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $response = $this->actingAs($user)->get('/pilot/dashboard');

    expect($response->status())->toBe(200);
});

test('pilot visiting /dashboard is redirected to pilot dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $response = $this->actingAs($user)->get('/dashboard');

    expect($response->status())->toBe(302);
    expect($response->headers->get('Location'))->toContain('/pilot/dashboard');
});

test('staff can access staff routes', function () {
    $user = User::factory()->create();
    $user->assignRole('STAFF_ADMINISTRATIF');

    $response = $this->actingAs($user)->get('/staff/dashboard');

    expect($response->status())->toBe(200);
});

test('guest cannot access protected routes', function () {
    $response = $this->get('/pilot/dashboard');
    expect($response->status())->toBe(302); // Redirect to login

    $response = $this->get('/staff/dashboard');
    expect($response->status())->toBe(302);

    $response = $this->get('/admin/home');
    expect($response->status())->toBe(302);
});

test('user helper methods work correctly', function () {
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    expect($admin->isAdmin())->toBeTrue();
    expect($admin->isPilot())->toBeFalse();
    expect($admin->isStaff())->toBeFalse();

    $pilot = User::factory()->create();
    $pilot->assignRole('PILOTE');
    expect($pilot->isPilot())->toBeTrue();
    expect($pilot->isAdmin())->toBeFalse();
    expect($pilot->isStaff())->toBeFalse();

    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ENTREE');
    expect($staff->isStaff())->toBeTrue();
    expect($staff->isPilot())->toBeFalse();
    expect($staff->isAdmin())->toBeFalse();
});

test('authenticated user is redirected based on role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');

    $response = $this->actingAs($admin)->get('/');
    expect($response->status())->toBe(302);
    expect($response->headers->get('Location'))->toContain('admin');

    $pilot = User::factory()->create();
    $pilot->assignRole('PILOTE');

    $response = $this->actingAs($pilot)->get('/');
    expect($response->status())->toBe(302);
    expect($response->headers->get('Location'))->toContain('pilot');
});
