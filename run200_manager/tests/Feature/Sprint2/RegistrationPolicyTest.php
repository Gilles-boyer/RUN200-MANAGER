<?php

use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

test('admin can create a race', function () {
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');

    expect((new \App\Policies\RacePolicy)->create($admin))->toBeTrue();
});

test('staff can create a race', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');

    expect((new \App\Policies\RacePolicy)->create($staff))->toBeTrue();
});

test('pilot cannot create a race', function () {
    $pilot = User::factory()->create();
    $pilot->assignRole('PILOTE');

    expect((new \App\Policies\RacePolicy)->create($pilot))->toBeFalse();
});

test('staff can validate a registration', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');

    $registration = RaceRegistration::factory()->create();

    expect((new \App\Policies\RaceRegistrationPolicy)->validate($staff, $registration))->toBeTrue();
});

test('pilot cannot validate a registration', function () {
    $pilot = User::factory()->create();
    $pilot->assignRole('PILOTE');

    $registration = RaceRegistration::factory()->create();

    expect((new \App\Policies\RaceRegistrationPolicy)->validate($pilot, $registration))->toBeFalse();
});

test('staff can assign paddock', function () {
    $staff = User::factory()->create();
    $staff->assignRole('STAFF_ADMINISTRATIF');

    $registration = RaceRegistration::factory()->create();

    expect((new \App\Policies\RaceRegistrationPolicy)->assignPaddock($staff, $registration))->toBeTrue();
});

test('pilot can view own registration', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');
    $pilot = \App\Models\Pilot::factory()->create(['user_id' => $user->id]);

    $registration = RaceRegistration::factory()->create(['pilot_id' => $pilot->id]);

    expect((new \App\Policies\RaceRegistrationPolicy)->view($user, $registration))->toBeTrue();
});

test('pilot cannot view another pilot registration', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');
    $pilot = \App\Models\Pilot::factory()->create(['user_id' => $user->id]);

    $otherPilot = \App\Models\Pilot::factory()->create();
    $registration = RaceRegistration::factory()->create(['pilot_id' => $otherPilot->id]);

    expect((new \App\Policies\RaceRegistrationPolicy)->view($user, $registration))->toBeFalse();
});

test('pilot can delete own pending registration', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');
    $pilot = \App\Models\Pilot::factory()->create(['user_id' => $user->id]);

    $registration = RaceRegistration::factory()->create([
        'pilot_id' => $pilot->id,
        'status' => 'PENDING_VALIDATION',
    ]);

    expect((new \App\Policies\RaceRegistrationPolicy)->delete($user, $registration))->toBeTrue();
});

test('pilot cannot delete own accepted registration', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');
    $pilot = \App\Models\Pilot::factory()->create(['user_id' => $user->id]);

    $registration = RaceRegistration::factory()->create([
        'pilot_id' => $pilot->id,
        'status' => 'ACCEPTED',
    ]);

    expect((new \App\Policies\RaceRegistrationPolicy)->delete($user, $registration))->toBeFalse();
});
