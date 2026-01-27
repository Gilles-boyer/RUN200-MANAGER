<?php

use App\Models\Pilot;
use App\Models\User;
use App\Policies\PilotPolicy;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);
    $this->policy = new PilotPolicy;
});

test('admin peut voir tous les pilotes', function () {
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $pilot = Pilot::factory()->create();

    expect($this->policy->view($admin, $pilot))->toBeTrue();
});

test('pilote peut voir son propre profil', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');
    $pilot = Pilot::factory()->create(['user_id' => $user->id]);

    expect($this->policy->view($user, $pilot))->toBeTrue();
});

test('pilote ne peut pas voir le profil d\'un autre pilote', function () {
    $user1 = User::factory()->create();
    $user1->assignRole('PILOTE');
    $pilot1 = Pilot::factory()->create(['user_id' => $user1->id]);

    $user2 = User::factory()->create();
    $pilot2 = Pilot::factory()->create(['user_id' => $user2->id]);

    expect($this->policy->view($user2, $pilot1))->toBeFalse();
});

test('admin peut mettre à jour tous les pilotes', function () {
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $pilot = Pilot::factory()->create();

    expect($this->policy->update($admin, $pilot))->toBeTrue();
});

test('pilote peut mettre à jour son propre profil', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');
    $pilot = Pilot::factory()->create(['user_id' => $user->id]);

    expect($this->policy->update($user, $pilot))->toBeTrue();
});

test('pilote ne peut pas mettre à jour le profil d\'un autre pilote', function () {
    $user1 = User::factory()->create();
    $user1->assignRole('PILOTE');
    $pilot1 = Pilot::factory()->create(['user_id' => $user1->id]);

    $user2 = User::factory()->create();
    $pilot2 = Pilot::factory()->create(['user_id' => $user2->id]);

    expect($this->policy->update($user2, $pilot1))->toBeFalse();
});
