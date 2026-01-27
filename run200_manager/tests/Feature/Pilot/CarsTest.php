<?php

use App\Livewire\Pilot\Cars\Form;
use App\Livewire\Pilot\Cars\Index;
use App\Models\Car;
use App\Models\CarCategory;
use App\Models\Pilot;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);
});

test('cars index redirects to profile edit when pilot profile missing', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $this->actingAs($user)
        ->get(route('pilot.cars.index'))
        ->assertRedirect(route('pilot.profile.edit'))
        ->assertSessionHas('info');
});

test('cars index renders empty state when no cars', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    Pilot::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('pilot.cars.index'))
        ->assertOk()
        ->assertSee('Mes Voitures')
        ->assertSee('Aucune voiture enregistrée');
});

test('pilot can create a car via livewire', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $pilot = Pilot::factory()->create(['user_id' => $user->id]);
    $category = CarCategory::factory()->create(['is_active' => true]);

    $this->actingAs($user);

    Livewire::test(Form::class)
        ->set('race_number', 42)
        ->set('make', 'Porsche')
        ->set('model', '911')
        ->set('car_category_id', $category->id)
        ->set('notes', 'Test notes')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('pilot.cars.index'));

    $this->assertDatabaseHas('cars', [
        'pilot_id' => $pilot->id,
        'car_category_id' => $category->id,
        'race_number' => 42,
        'make' => 'Porsche',
        'model' => '911',
    ]);
});

test('car race number must be unique', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $pilot = Pilot::factory()->create(['user_id' => $user->id]);
    $category = CarCategory::factory()->create(['is_active' => true]);

    Car::factory()->create([
        'pilot_id' => $pilot->id,
        'car_category_id' => $category->id,
        'race_number' => 77,
    ]);

    $this->actingAs($user);

    Livewire::test(Form::class)
        ->set('race_number', 77)
        ->set('make', 'BMW')
        ->set('model', 'M3')
        ->set('car_category_id', $category->id)
        ->call('save')
        ->assertHasErrors(['race_number']);
});

test('pilot cannot edit another pilot car', function () {
    $owner = User::factory()->create();
    $owner->assignRole('PILOTE');
    $ownerPilot = Pilot::factory()->create(['user_id' => $owner->id]);

    $other = User::factory()->create();
    $other->assignRole('PILOTE');
    Pilot::factory()->create(['user_id' => $other->id]);

    $car = Car::factory()->create(['pilot_id' => $ownerPilot->id]);

    $this->actingAs($other)
        ->get(route('pilot.cars.edit', $car->id))
        ->assertForbidden();
});

test('pilot cannot delete another pilot car from index component', function () {
    $owner = User::factory()->create();
    $owner->assignRole('PILOTE');
    $ownerPilot = Pilot::factory()->create(['user_id' => $owner->id]);

    $other = User::factory()->create();
    $other->assignRole('PILOTE');
    $otherPilot = Pilot::factory()->create(['user_id' => $other->id]);

    $car = Car::factory()->create(['pilot_id' => $ownerPilot->id]);

    $this->actingAs($other);

    Livewire::test(Index::class)
        ->call('deleteCar', $car->id);

    $this->assertDatabaseHas('cars', ['id' => $car->id]);
});
