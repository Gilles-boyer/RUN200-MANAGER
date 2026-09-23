<?php

use App\Livewire\Admin\Dashboard;
use App\Models\Car;
use App\Models\CarCategory;
use App\Models\Pilot;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

test('category statistics keep aligned list labels and include every car', function () {
    $pilot = Pilot::factory()->create();
    for ($count = 1; $count <= 10; $count++) {
        $category = CarCategory::factory()->create(['name' => "CATÉGORIE {$count}"]);
        Car::factory()->for($pilot)->for($category, 'category')->count($count)->create();
    }
    CarCategory::factory()->create(['name' => 'CATÉGORIE VIDE']);

    $statistics = (new Dashboard)->carsByCategory();

    expect(array_is_list($statistics['labels']))->toBeTrue()
        ->and(array_is_list($statistics['data']))->toBeTrue()
        ->and($statistics['labels'])->toHaveCount(9)
        ->and($statistics['labels'][0])->toBe('CATÉGORIE 10')
        ->and($statistics['data'][0])->toBe(10)
        ->and($statistics['labels'][8])->toBe('Autres catégories')
        ->and($statistics['data'][8])->toBe(3)
        ->and($statistics['total'])->toBe(55)
        ->and($statistics['category_count'])->toBe(10)
        ->and(array_sum($statistics['data']))->toBe(Car::count())
        ->and($statistics['breakdown'])->toHaveCount(10);
});

test('admin dashboard explains the grouped chart and shows exact category counts', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');
    $pilot = Pilot::factory()->create();
    for ($index = 1; $index <= 9; $index++) {
        $category = CarCategory::factory()->create(['name' => "ESSAI {$index}"]);
        Car::factory()->for($pilot)->for($category, 'category')->create();
    }

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('9 voitures dans 9 catégories')
        ->assertSee('Autres catégories')
        ->assertSee('ESSAI 9');
});
