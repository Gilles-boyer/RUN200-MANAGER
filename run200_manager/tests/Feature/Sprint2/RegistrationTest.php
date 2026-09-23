<?php

use App\Application\Registrations\UseCases\SubmitRegistration;
use App\Models\Car;
use App\Models\CarCategory;
use App\Models\Payment;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\Season;
use App\Models\User;
use Database\Seeders\CarCategoriesSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(CarCategoriesSeeder::class);
});

test('season can have multiple races', function () {
    $season = Season::factory()->create();
    $race1 = Race::factory()->create(['season_id' => $season->id]);
    $race2 = Race::factory()->create(['season_id' => $season->id]);

    expect($season->races)->toHaveCount(2);
    expect($race1->season->id)->toBe($season->id);
});

test('race status transitions work correctly', function () {
    $race = Race::factory()->create(['status' => 'DRAFT']);

    expect($race->isDraft())->toBeTrue();
    expect($race->isOpen())->toBeFalse();
    expect($race->isClosed())->toBeFalse();

    $race->update(['status' => 'OPEN']);
    expect($race->isOpen())->toBeTrue();

    $race->update(['status' => 'CLOSED']);
    expect($race->isClosed())->toBeTrue();
});

test('pilot can register to an open race', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $pilot = Pilot::factory()->create(['user_id' => $user->id]);
    $category = CarCategory::first();
    $car = Car::factory()->create(['pilot_id' => $pilot->id, 'car_category_id' => $category->id]);

    $race = Race::factory()->open()->create();

    $useCase = new SubmitRegistration;
    // With requiresPayment=true (default for online registration), status is PENDING_PAYMENT
    $registration = $useCase->execute($race, $pilot, $car, true);

    expect($registration)->toBeInstanceOf(RaceRegistration::class);
    expect($registration->status)->toBe('PENDING_PAYMENT');
    expect($registration->race_id)->toBe($race->id);
    expect($registration->pilot_id)->toBe($pilot->id);
    expect($registration->car_id)->toBe($car->id);
});

test('pilot cannot register to a closed race', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $pilot = Pilot::factory()->create(['user_id' => $user->id]);
    $category = CarCategory::first();
    $car = Car::factory()->create(['pilot_id' => $pilot->id, 'car_category_id' => $category->id]);

    $race = Race::factory()->closed()->create();

    $useCase = new SubmitRegistration;

    expect(fn () => $useCase->execute($race, $pilot, $car))
        ->toThrow(InvalidArgumentException::class, 'La course n\'est pas ouverte aux inscriptions.');
});

test('pilot can register multiple cars to the same race', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $pilot = Pilot::factory()->create(['user_id' => $user->id]);
    $category = CarCategory::first();
    $car1 = Car::factory()->create(['pilot_id' => $pilot->id, 'car_category_id' => $category->id]);
    $car2 = Car::factory()->create(['pilot_id' => $pilot->id, 'car_category_id' => $category->id]);

    $race = Race::factory()->open()->create();

    $useCase = new SubmitRegistration;
    $registration1 = $useCase->execute($race, $pilot, $car1);
    $registration2 = $useCase->execute($race, $pilot, $car2);

    // Un pilote peut inscrire plusieurs voitures à la même course
    expect($registration1)->toBeInstanceOf(RaceRegistration::class);
    expect($registration2)->toBeInstanceOf(RaceRegistration::class);
    expect($registration1->car_id)->toBe($car1->id);
    expect($registration2->car_id)->toBe($car2->id);
    expect(RaceRegistration::where('race_id', $race->id)->where('pilot_id', $pilot->id)->count())->toBe(2);
});

test('same car cannot be registered twice to the same race', function () {
    $user1 = User::factory()->create();
    $user1->assignRole('PILOTE');
    $pilot1 = Pilot::factory()->create(['user_id' => $user1->id]);

    $user2 = User::factory()->create();
    $user2->assignRole('PILOTE');
    $pilot2 = Pilot::factory()->create(['user_id' => $user2->id]);

    $category = CarCategory::first();
    $car = Car::factory()->create(['pilot_id' => $pilot1->id, 'car_category_id' => $category->id]);

    $race = Race::factory()->open()->create();

    $useCase = new SubmitRegistration;
    $useCase->execute($race, $pilot1, $car);

    // Changer temporairement le propriétaire de la voiture pour tester
    $car->update(['pilot_id' => $pilot2->id]);

    expect(fn () => $useCase->execute($race, $pilot2, $car))
        ->toThrow(InvalidArgumentException::class, 'Cette voiture est déjà inscrite à cette course.');
});

test('pilot cannot register with another pilot car', function () {
    $user1 = User::factory()->create();
    $user1->assignRole('PILOTE');
    $pilot1 = Pilot::factory()->create(['user_id' => $user1->id]);

    $user2 = User::factory()->create();
    $user2->assignRole('PILOTE');
    $pilot2 = Pilot::factory()->create(['user_id' => $user2->id]);

    $category = CarCategory::first();
    $car = Car::factory()->create(['pilot_id' => $pilot1->id, 'car_category_id' => $category->id]);

    $race = Race::factory()->open()->create();

    $useCase = new SubmitRegistration;

    expect(fn () => $useCase->execute($race, $pilot2, $car))
        ->toThrow(InvalidArgumentException::class, 'Cette voiture ne vous appartient pas.');
});

test('registration has correct relationships', function () {
    $user = User::factory()->create();
    $user->assignRole('PILOTE');

    $pilot = Pilot::factory()->create(['user_id' => $user->id]);
    $category = CarCategory::first();
    $car = Car::factory()->create(['pilot_id' => $pilot->id, 'car_category_id' => $category->id]);

    $race = Race::factory()->open()->create();

    $registration = RaceRegistration::create([
        'race_id' => $race->id,
        'pilot_id' => $pilot->id,
        'car_id' => $car->id,
        'status' => 'PENDING_VALIDATION',
    ]);

    expect($registration->race->id)->toBe($race->id);
    expect($registration->pilot->id)->toBe($pilot->id);
    expect($registration->car->id)->toBe($car->id);
    expect($registration->isPending())->toBeTrue();
});

test('registration status methods work correctly', function () {
    $registration = RaceRegistration::factory()->create(['status' => 'PENDING_VALIDATION']);
    expect($registration->isPending())->toBeTrue();
    expect($registration->isAccepted())->toBeFalse();
    expect($registration->isRefused())->toBeFalse();

    $registration->update(['status' => 'ACCEPTED']);
    expect($registration->isAccepted())->toBeTrue();

    $registration->update(['status' => 'REFUSED']);
    expect($registration->isRefused())->toBeTrue();
});

test('a refused registration is reactivated on the same row and preserves its paid payment', function () {
    $pilot = Pilot::factory()->create();
    $category = CarCategory::first();
    $car = Car::factory()->for($pilot)->for($category, 'category')->create();
    $race = Race::factory()->open()->create();
    $registration = RaceRegistration::factory()->for($race)->for($pilot)->for($car)->create([
        'status' => 'REFUSED',
        'reason' => 'Document manquant',
    ]);
    $payment = Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $pilot->user_id,
        'amount' => 50,
        'amount_cents' => 5000,
        'method' => 'manual',
        'status' => 'paid',
    ]);

    $reactivated = (new SubmitRegistration)->execute($race, $pilot, $car);

    expect($reactivated->id)->toBe($registration->id)
        ->and($reactivated->status)->toBe('PENDING_VALIDATION')
        ->and($reactivated->reason)->toBeNull()
        ->and($reactivated->car_category_id)->toBe($category->id)
        ->and($payment->fresh()->status->value)->toBe('paid')
        ->and(RaceRegistration::where('race_id', $race->id)->where('car_id', $car->id)->count())->toBe(1);
});

test('a cancelled registration with a refunded payment requires payment again', function () {
    $pilot = Pilot::factory()->create();
    $car = Car::factory()->for($pilot)->for(CarCategory::first(), 'category')->create();
    $race = Race::factory()->open()->create();
    $registration = RaceRegistration::factory()->for($race)->for($pilot)->for($car)->create(['status' => 'CANCELLED']);
    Payment::create([
        'race_registration_id' => $registration->id,
        'user_id' => $pilot->user_id,
        'amount' => 50,
        'amount_cents' => 5000,
        'method' => 'manual',
        'status' => 'refunded',
    ]);

    $reactivated = (new SubmitRegistration)->execute($race, $pilot, $car);

    expect($reactivated->id)->toBe($registration->id)
        ->and($reactivated->status)->toBe('PENDING_PAYMENT');
});
