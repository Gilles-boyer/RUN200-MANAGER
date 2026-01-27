<?php

use App\Models\Car;
use App\Models\CarCategory;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\Season;
use App\Models\User;

describe('User Data Formatting', function () {
    it('formate le nom en Title Case', function () {
        $user = User::factory()->create(['name' => 'jean dupont']);
        expect($user->name)->toBe('Jean Dupont');
    });

    it('formate l\'email en minuscules', function () {
        $user = User::factory()->create(['email' => 'TEST@EXAMPLE.COM']);
        expect($user->email)->toBe('test@example.com');
    });
});

describe('Pilot Data Formatting', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    it('formate le prénom en Title Case', function () {
        $pilot = Pilot::create([
            'user_id' => $this->user->id,
            'first_name' => 'JEAN-PIERRE',
            'last_name' => 'Dupont',
            'license_number' => '123456',
            'birth_date' => '1990-01-01',
            'birth_place' => 'Paris',
            'phone' => '0612345678',
            'address' => '1 rue test',
            'city' => 'Paris',
            'postal_code' => '75001',
            'emergency_contact_name' => 'Contact',
            'emergency_contact_phone' => '0698765432',
        ]);

        expect($pilot->first_name)->toBe('Jean-Pierre');
    });

    it('formate le nom de famille en majuscules', function () {
        $pilot = Pilot::create([
            'user_id' => $this->user->id,
            'first_name' => 'Jean',
            'last_name' => 'dupont',
            'license_number' => '123457',
            'birth_date' => '1990-01-01',
            'birth_place' => 'Paris',
            'phone' => '0612345678',
            'address' => '1 rue test',
            'city' => 'Paris',
            'postal_code' => '75001',
            'emergency_contact_name' => 'Contact',
            'emergency_contact_phone' => '0698765432',
        ]);

        expect($pilot->last_name)->toBe('DUPONT');
    });

    it('formate la ville en majuscules', function () {
        $pilot = Pilot::create([
            'user_id' => $this->user->id,
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'license_number' => '123458',
            'birth_date' => '1990-01-01',
            'birth_place' => 'paris',
            'phone' => '0612345678',
            'address' => '1 rue test',
            'city' => 'lyon',
            'postal_code' => '69001',
            'emergency_contact_name' => 'Contact',
            'emergency_contact_phone' => '0698765432',
        ]);

        expect($pilot->city)->toBe('LYON');
    });

    it('formate le code postal avec padding', function () {
        $pilot = Pilot::create([
            'user_id' => $this->user->id,
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'license_number' => '123459',
            'birth_date' => '1990-01-01',
            'birth_place' => 'Paris',
            'phone' => '0612345678',
            'address' => '1 rue test',
            'city' => 'Paris',
            'postal_code' => '1000',
            'emergency_contact_name' => 'Contact',
            'emergency_contact_phone' => '0698765432',
        ]);

        expect($pilot->postal_code)->toBe('01000');
    });

    it('normalise le numéro de téléphone', function () {
        $pilot = Pilot::create([
            'user_id' => $this->user->id,
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'license_number' => '123460',
            'birth_date' => '1990-01-01',
            'birth_place' => 'Paris',
            'phone' => '+33 6 12 34 56 78',
            'address' => '1 rue test',
            'city' => 'Paris',
            'postal_code' => '75001',
            'emergency_contact_name' => 'Contact',
            'emergency_contact_phone' => '06.98.76.54.32',
        ]);

        // En base, stocké normalisé
        expect($pilot->getRawOriginal('phone'))->toBe('0612345678');
        expect($pilot->getRawOriginal('emergency_contact_phone'))->toBe('0698765432');

        // À l'affichage, formaté
        expect($pilot->phone)->toBe('06 12 34 56 78');
        expect($pilot->emergency_contact_phone)->toBe('06 98 76 54 32');
    });

    it('nettoie le numéro de licence', function () {
        $pilot = Pilot::create([
            'user_id' => $this->user->id,
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'license_number' => '12 34 56',
            'birth_date' => '1990-01-01',
            'birth_place' => 'Paris',
            'phone' => '0612345678',
            'address' => '1 rue test',
            'city' => 'Paris',
            'postal_code' => '75001',
            'emergency_contact_name' => 'Contact',
            'emergency_contact_phone' => '0698765432',
        ]);

        expect($pilot->license_number)->toBe('123456');
    });
});

describe('Car Data Formatting', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->pilot = Pilot::create([
            'user_id' => $this->user->id,
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'license_number' => '999999',
            'birth_date' => '1990-01-01',
            'birth_place' => 'Paris',
            'phone' => '0612345678',
            'address' => '1 rue test',
            'city' => 'Paris',
            'postal_code' => '75001',
            'emergency_contact_name' => 'Contact',
            'emergency_contact_phone' => '0698765432',
        ]);
        $this->category = CarCategory::create([
            'name' => 'CATEGORIE A',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    });

    it('formate la marque connue correctement', function () {
        $car = Car::create([
            'pilot_id' => $this->pilot->id,
            'car_category_id' => $this->category->id,
            'race_number' => 1,
            'make' => 'bmw',
            'model' => 'M3',
        ]);

        expect($car->make)->toBe('BMW');
    });

    it('formate la marque composée correctement', function () {
        $car = Car::create([
            'pilot_id' => $this->pilot->id,
            'car_category_id' => $this->category->id,
            'race_number' => 2,
            'make' => 'alfa romeo',
            'model' => 'Giulia',
        ]);

        expect($car->make)->toBe('Alfa Romeo');
    });

    it('formate le modèle en Title Case', function () {
        $car = Car::create([
            'pilot_id' => $this->pilot->id,
            'car_category_id' => $this->category->id,
            'race_number' => 3,
            'make' => 'Peugeot',
            'model' => '308 GTI',
        ]);

        expect($car->model)->toBe('308 Gti');
    });
});

describe('Race Data Formatting', function () {
    beforeEach(function () {
        $this->season = Season::create([
            'name' => 'Saison 2024',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'is_active' => true,
        ]);
    });

    it('formate le nom en Title Case', function () {
        $race = Race::create([
            'season_id' => $this->season->id,
            'name' => 'GRAND PRIX DE PARIS',
            'race_date' => '2024-06-15',
            'location' => 'Paris',
            'status' => 'DRAFT',
        ]);

        expect($race->name)->toBe('Grand Prix De Paris');
    });

    it('formate le lieu en majuscules', function () {
        $race = Race::create([
            'season_id' => $this->season->id,
            'name' => 'Grand Prix',
            'race_date' => '2024-06-15',
            'location' => 'circuit paul ricard',
            'status' => 'DRAFT',
        ]);

        expect($race->location)->toBe('CIRCUIT PAUL RICARD');
    });
});

describe('CarCategory Data Formatting', function () {
    it('formate le nom en majuscules', function () {
        $category = CarCategory::create([
            'name' => 'catégorie sport',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        expect($category->name)->toBe('CATÉGORIE SPORT');
    });
});
