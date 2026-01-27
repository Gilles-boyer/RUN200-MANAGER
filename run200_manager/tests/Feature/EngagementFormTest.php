<?php

use App\Models\Car;
use App\Models\CarCategory;
use App\Models\EngagementForm;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\Season;
use App\Models\User;

describe('EngagementForm Model', function () {
    it('can create an engagement form from a registration', function () {
        $user = User::factory()->create();
        $pilot = Pilot::factory()->create(['user_id' => $user->id]);
        $category = CarCategory::create(['name' => 'SPORT', 'is_active' => true, 'sort_order' => 1]);
        $car = Car::factory()->create(['pilot_id' => $pilot->id, 'car_category_id' => $category->id]);
        $season = Season::factory()->create(['is_active' => true]);
        $race = Race::create([
            'season_id' => $season->id,
            'name' => 'Course Test',
            'race_date' => now()->addDays(7),
            'location' => 'CIRCUIT TEST',
            'status' => 'OPEN',
        ]);

        $registration = RaceRegistration::create([
            'race_id' => $race->id,
            'pilot_id' => $pilot->id,
            'car_id' => $car->id,
            'status' => 'ACCEPTED',
        ]);

        $witness = User::factory()->create();
        $signatureData = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $engagement = EngagementForm::createFromRegistration(
            $registration,
            $signatureData,
            $witness->id,
            null,
            '127.0.0.1',
            'Test User Agent'
        );

        expect($engagement)->toBeInstanceOf(EngagementForm::class)
            ->and($engagement->pilot_name)->toBe($pilot->full_name)
            ->and($engagement->car_make)->toBe($car->make)
            ->and($engagement->car_model)->toBe($car->model)
            ->and($engagement->race_name)->toBe($race->name)
            ->and($engagement->witnessed_by)->toBe($witness->id)
            ->and($engagement->signature_data)->toBe($signatureData);
    });

    it('stores guardian info for minor pilots', function () {
        $user = User::factory()->create();
        $pilot = Pilot::factory()->create([
            'user_id' => $user->id,
            'is_minor' => true,
            'guardian_first_name' => 'Jean',
            'guardian_last_name' => 'DUPONT',
        ]);
        $category = CarCategory::create(['name' => 'GT', 'is_active' => true, 'sort_order' => 1]);
        $car = Car::factory()->create(['pilot_id' => $pilot->id, 'car_category_id' => $category->id]);
        $season = Season::factory()->create(['is_active' => true]);
        $race = Race::create([
            'season_id' => $season->id,
            'name' => 'Course Mineurs',
            'race_date' => now()->addDays(7),
            'location' => 'CIRCUIT',
            'status' => 'OPEN',
        ]);

        $registration = RaceRegistration::create([
            'race_id' => $race->id,
            'pilot_id' => $pilot->id,
            'car_id' => $car->id,
            'status' => 'ACCEPTED',
        ]);

        $witness = User::factory()->create();
        $signatureData = 'data:image/png;base64,test';
        $guardianSignatureData = 'data:image/png;base64,guardian';

        $engagement = EngagementForm::createFromRegistration(
            $registration,
            $signatureData,
            $witness->id,
            $guardianSignatureData,
            '127.0.0.1'
        );

        expect($engagement->is_minor)->toBeTrue()
            ->and($engagement->guardian_name)->toBe('Jean DUPONT')
            ->and($engagement->guardian_signature_data)->toBe($guardianSignatureData);
    });

    it('has relationship with registration', function () {
        $engagement = EngagementForm::factory()->create();

        expect($engagement->registration)->toBeInstanceOf(RaceRegistration::class);
    });

    it('has relationship with witness', function () {
        $witness = User::factory()->create();
        $engagement = EngagementForm::factory()->create(['witnessed_by' => $witness->id]);

        expect($engagement->witness)->toBeInstanceOf(User::class)
            ->and($engagement->witness->id)->toBe($witness->id);
    });

    it('can check if form is complete', function () {
        $engagement = EngagementForm::factory()->create([
            'is_minor' => false,
            'signature_data' => 'data:image/png;base64,test',
        ]);

        expect($engagement->isComplete())->toBeTrue();
    });

    it('requires guardian signature for minors to be complete', function () {
        $engagement = EngagementForm::factory()->create([
            'is_minor' => true,
            'signature_data' => 'data:image/png;base64,test',
            'guardian_signature_data' => null,
        ]);

        expect($engagement->isComplete())->toBeFalse();

        $engagement->guardian_signature_data = 'data:image/png;base64,guardian';

        expect($engagement->isComplete())->toBeTrue();
    });

    it('can scope by race', function () {
        $season = Season::factory()->create(['is_active' => true]);
        $race1 = Race::create([
            'season_id' => $season->id,
            'name' => 'Course 1',
            'race_date' => now()->addDays(7),
            'location' => 'CIRCUIT 1',
            'status' => 'OPEN',
        ]);
        $race2 = Race::create([
            'season_id' => $season->id,
            'name' => 'Course 2',
            'race_date' => now()->addDays(14),
            'location' => 'CIRCUIT 2',
            'status' => 'OPEN',
        ]);

        $user = User::factory()->create();
        $pilot = Pilot::factory()->create(['user_id' => $user->id]);
        $car = Car::factory()->create(['pilot_id' => $pilot->id]);

        $reg1 = RaceRegistration::create([
            'race_id' => $race1->id,
            'pilot_id' => $pilot->id,
            'car_id' => $car->id,
            'status' => 'ACCEPTED',
        ]);

        $reg2 = RaceRegistration::create([
            'race_id' => $race2->id,
            'pilot_id' => $pilot->id,
            'car_id' => $car->id,
            'status' => 'ACCEPTED',
        ]);

        EngagementForm::factory()->create(['race_registration_id' => $reg1->id]);
        EngagementForm::factory()->create(['race_registration_id' => $reg2->id]);

        expect(EngagementForm::forRace($race1->id)->count())->toBe(1)
            ->and(EngagementForm::forRace($race2->id)->count())->toBe(1);
    });

    it('can get car display name', function () {
        $engagement = EngagementForm::factory()->create([
            'car_make' => 'BMW',
            'car_model' => 'M3',
            'car_race_number' => 42,
        ]);

        expect($engagement->car_display_name)->toBe('BMW M3 #42');
    });
});

describe('RaceRegistration Engagement Relationship', function () {
    it('has one engagement form', function () {
        $registration = RaceRegistration::factory()->create(['status' => 'ACCEPTED']);
        $engagement = EngagementForm::factory()->create(['race_registration_id' => $registration->id]);

        expect($registration->engagementForm)->toBeInstanceOf(EngagementForm::class)
            ->and($registration->engagementForm->id)->toBe($engagement->id);
    });

    it('registration without engagement returns null', function () {
        $registration = RaceRegistration::factory()->create(['status' => 'ACCEPTED']);

        expect($registration->engagementForm)->toBeNull();
    });
});
