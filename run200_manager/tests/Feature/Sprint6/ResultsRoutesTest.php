<?php

declare(strict_types=1);

use App\Models\Race;
use App\Models\RaceResult;
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
        'race.manage',
        'results.import',
        'results.publish',
    ];
    foreach ($permissions as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    // Assign permissions to roles
    $adminRole = Role::findByName('ADMIN', 'web');
    $adminRole->syncPermissions(['race.manage', 'results.import', 'results.publish']);

    $staffAdminRole = Role::findByName('STAFF_ADMINISTRATIF', 'web');
    $staffAdminRole->syncPermissions(['race.manage']);
});

describe('Staff Results Routes', function () {

    it('allows admin to access results manager', function () {
        $user = User::factory()->create();
        $user->assignRole('ADMIN');

        $race = Race::factory()->closed()->create();

        $response = $this->actingAs($user)->get(route('staff.races.results', $race));

        expect($response->status())->toBe(200);
    });

    it('allows staff with race.manage permission to access results', function () {
        $user = User::factory()->create();
        $user->assignRole('STAFF_ADMINISTRATIF');

        $race = Race::factory()->closed()->create();

        $response = $this->actingAs($user)->get(route('staff.races.results', $race));

        expect($response->status())->toBe(200);
    });

    it('denies access to users without permission', function () {
        $user = User::factory()->create();
        $user->assignRole('PILOTE');

        $race = Race::factory()->create();

        $response = $this->actingAs($user)->get(route('staff.races.results', $race));

        expect($response->status())->toBeIn([403, 302]); // Forbidden or redirect
    });

    it('denies access to guests', function () {
        $race = Race::factory()->create();

        $response = $this->get(route('staff.races.results', $race));

        expect($response->status())->toBeIn([401, 302]); // Unauthorized or redirect to login
    });

});

describe('Pilot Results Routes', function () {

    it('allows pilot to view published race results', function () {
        $user = User::factory()->create();
        $user->assignRole('PILOTE');

        $race = Race::factory()->published()->create();
        RaceResult::factory()->forRace($race)->count(5)->create();

        $response = $this->actingAs($user)->get(route('pilot.results.race', $race));

        expect($response->status())->toBe(200);
    });

    it('returns 404 for unpublished race results', function () {
        $user = User::factory()->create();
        $user->assignRole('PILOTE');

        $race = Race::factory()->resultsReady()->create();

        $response = $this->actingAs($user)->get(route('pilot.results.race', $race));

        expect($response->status())->toBe(404);
    });

    it('returns 404 for race with CLOSED status', function () {
        $user = User::factory()->create();
        $user->assignRole('PILOTE');

        $race = Race::factory()->closed()->create();

        $response = $this->actingAs($user)->get(route('pilot.results.race', $race));

        expect($response->status())->toBe(404);
    });

    it('denies access to guests', function () {
        $race = Race::factory()->published()->create();

        $response = $this->get(route('pilot.results.race', $race));

        expect($response->status())->toBeIn([401, 302]); // Unauthorized or redirect to login
    });

});
