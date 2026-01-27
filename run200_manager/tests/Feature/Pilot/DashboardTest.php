<?php

use App\Models\Car;
use App\Models\Pilot;
use App\Models\User;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);
});

test('pilot dashboard renders even without pilot profile', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $this->actingAs($user)
        ->get(route('pilot.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Profil pilote non créé');
});

test('pilot dashboard shows quick actions when profile is complete', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $pilot = Pilot::factory()->complete()->create([
        'user_id' => $user->id,
        'is_active_season' => true,
    ]);

    Car::factory()->count(2)->create([
        'pilot_id' => $pilot->id,
    ]);

    $this->actingAs($user)
        ->get(route('pilot.dashboard'))
        ->assertOk()
        ->assertSee('Actions Rapides')
        ->assertSee('Mes Voitures');
});
