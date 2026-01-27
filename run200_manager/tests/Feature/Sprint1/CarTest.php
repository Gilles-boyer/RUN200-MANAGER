<?php

use App\Models\Car;
use App\Models\CarCategory;
use App\Models\Pilot;

test('une voiture appartient à un pilote et une catégorie', function () {
    $car = Car::factory()->create();

    expect($car->pilot)->not->toBeNull()
        ->and($car->category)->not->toBeNull()
        ->and($car->pilot)->toBeInstanceOf(Pilot::class)
        ->and($car->category)->toBeInstanceOf(CarCategory::class);
});

test('race_number est unique et entre 0 et 999', function () {
    $car1 = Car::factory()->create(['race_number' => 42]);

    expect($car1->race_number->toInt())->toBe(42);

    // Test unicité
    expect(fn () => Car::factory()->create(['race_number' => 42]))
        ->toThrow(Exception::class);
});

test('race_number ne peut pas être négatif', function () {
    expect(fn () => Car::factory()->create(['race_number' => -1]))
        ->toThrow(Exception::class);
});

test('race_number ne peut pas dépasser 999', function () {
    expect(fn () => Car::factory()->create(['race_number' => 1000]))
        ->toThrow(Exception::class);
});

test('une voiture enregistre son activité', function () {
    $car = Car::factory()->create(['make' => 'Porsche', 'model' => '911 GT3']);

    expect($car->activities()->count())->toBeGreaterThan(0);
});

test('une catégorie peut avoir plusieurs voitures', function () {
    $category = CarCategory::factory()->hasCars(5)->create();

    expect($category->cars)->toHaveCount(5);
});

test('scope whereActive retourne uniquement les catégories actives', function () {
    CarCategory::factory()->create(['is_active' => true]);
    CarCategory::factory()->create(['is_active' => false]);

    $activeCategories = CarCategory::whereActive()->get();

    expect($activeCategories)->toHaveCount(1);
});

test('scope ordered trie par sort_order', function () {
    CarCategory::factory()->create(['sort_order' => 3, 'name' => 'C']);
    CarCategory::factory()->create(['sort_order' => 1, 'name' => 'A']);
    CarCategory::factory()->create(['sort_order' => 2, 'name' => 'B']);

    $orderedCategories = CarCategory::ordered()->get();

    expect($orderedCategories->first()->name)->toBe('A')
        ->and($orderedCategories->last()->name)->toBe('C');
});
