<?php

use App\Models\Car;
use App\Models\User;
use App\Policies\CarPolicy;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);
    $this->policy = new CarPolicy;
});

test('admin peut gérer toutes les voitures', function () {
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $car = Car::factory()->create();

    expect($this->policy->view($admin, $car))->toBeTrue()
        ->and($this->policy->update($admin, $car))->toBeTrue()
        ->and($this->policy->delete($admin, $car))->toBeTrue();
});

test('propriétaire peut gérer ses propres voitures', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');
    $pilot = \App\Models\Pilot::factory()->create([
        'user_id' => $user->id,
        'license_number' => '12345',
    ]);
    $car = Car::factory()->create(['pilot_id' => $pilot->id]);

    expect($this->policy->view($user, $car))->toBeTrue()
        ->and($this->policy->update($user, $car))->toBeTrue()
        ->and($this->policy->delete($user, $car))->toBeTrue();
});

test('utilisateur ne peut pas gérer les voitures d\'un autre pilote', function () {
    $user1 = User::factory()->create();
    $pilot1 = \App\Models\Pilot::factory()->create([
        'user_id' => $user1->id,
        'license_number' => '11111',
    ]);

    $user2 = User::factory()->create();
    $pilot2 = \App\Models\Pilot::factory()->create([
        'user_id' => $user2->id,
        'license_number' => '22222',
    ]);
    $car = Car::factory()->create(['pilot_id' => $pilot2->id]);

    expect($this->policy->view($user1, $car))->toBeFalse()
        ->and($this->policy->update($user1, $car))->toBeFalse()
        ->and($this->policy->delete($user1, $car))->toBeFalse();
});

test('pilote peut créer une nouvelle voiture', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');
    \App\Models\Pilot::factory()->create([
        'user_id' => $user->id,
        'license_number' => '12345',
    ]);

    expect($this->policy->create($user))->toBeTrue();
});

test('utilisateur sans pilote ne peut pas créer de voiture', function () {
    $user = User::factory()->create();

    expect($this->policy->create($user))->toBeFalse();
});
