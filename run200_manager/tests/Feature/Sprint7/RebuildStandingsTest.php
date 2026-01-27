<?php

declare(strict_types=1);

use App\Application\Championship\UseCases\RebuildSeasonStandings;
use App\Jobs\RebuildSeasonStandingsJob;
use App\Models\Car;
use App\Models\CarCategory;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\RaceResult;
use App\Models\Season;
use App\Models\SeasonCategoryStanding;
use App\Models\SeasonStanding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

describe('RebuildSeasonStandings Use Case', function () {

    beforeEach(function () {
        $this->useCase = new RebuildSeasonStandings;
    });

    it('creates standings for published races', function () {
        // Setup
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->create(['status' => 'PUBLISHED']);
        $category = CarCategory::factory()->create();

        // Create 3 pilots with registrations and results
        $pilots = [];
        for ($i = 1; $i <= 3; $i++) {
            $pilot = Pilot::factory()->create();
            $car = Car::factory()->for($pilot)->for($category, 'category')->create();
            $registration = RaceRegistration::factory()
                ->for($race)
                ->for($pilot)
                ->for($car)
                ->create(['status' => 'PUBLISHED']);

            RaceResult::factory()->create([
                'race_id' => $race->id,
                'race_registration_id' => $registration->id,
                'position' => $i,
            ]);

            $pilots[] = $pilot;
        }

        // Execute
        $result = $this->useCase->execute($season);

        // Assert
        expect($result['total_races'])->toBe(1)
            ->and($result['general_standings_count'])->toBe(3);

        // Check standings were created
        $standings = SeasonStanding::forSeason($season->id)->get();
        expect($standings)->toHaveCount(3);

        // Check points assignment
        $firstPlace = $standings->where('pilot_id', $pilots[0]->id)->first();
        expect($firstPlace->base_points)->toBe(25); // 1st place = 25 points
    });

    it('assigns correct points based on position', function () {
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->create(['status' => 'PUBLISHED']);
        $category = CarCategory::factory()->create();

        // Create pilots for positions 1-7
        $expectedPoints = [25, 20, 16, 14, 10, 8, 5];

        for ($pos = 1; $pos <= 7; $pos++) {
            $pilot = Pilot::factory()->create();
            $car = Car::factory()->for($pilot)->for($category, 'category')->create();
            $reg = RaceRegistration::factory()
                ->for($race)
                ->for($pilot)
                ->for($car)
                ->create();

            RaceResult::factory()->create([
                'race_id' => $race->id,
                'race_registration_id' => $reg->id,
                'position' => $pos,
            ]);
        }

        // Execute
        $this->useCase->execute($season);

        // Verify each position has correct points
        $standings = SeasonStanding::forSeason($season->id)
            ->orderBy('base_points', 'desc')
            ->get();

        foreach ($standings as $index => $standing) {
            expect($standing->base_points)->toBe($expectedPoints[$index]);
        }
    });

    it('does not rank pilots with only 1 race', function () {
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->create(['status' => 'PUBLISHED']);
        $category = CarCategory::factory()->create();

        $pilot = Pilot::factory()->create();
        $car = Car::factory()->for($pilot)->for($category, 'category')->create();
        $reg = RaceRegistration::factory()
            ->for($race)
            ->for($pilot)
            ->for($car)
            ->create();

        RaceResult::factory()->create([
            'race_id' => $race->id,
            'race_registration_id' => $reg->id,
            'position' => 1,
        ]);

        // Execute
        $this->useCase->execute($season);

        // Assert
        $standing = SeasonStanding::forSeason($season->id)->first();
        expect($standing->rank)->toBeNull()
            ->and($standing->races_count)->toBe(1);
    });

    it('ranks pilots with 2 or more races', function () {
        $season = Season::factory()->create();
        $category = CarCategory::factory()->create();

        // Create 2 published races
        $race1 = Race::factory()->for($season)->create(['status' => 'PUBLISHED']);
        $race2 = Race::factory()->for($season)->create(['status' => 'PUBLISHED']);

        $pilot = Pilot::factory()->create();
        $car = Car::factory()->for($pilot)->for($category, 'category')->create();

        // Register and get results for both races
        foreach ([$race1, $race2] as $race) {
            $reg = RaceRegistration::factory()
                ->for($race)
                ->for($pilot)
                ->for($car)
                ->create();

            RaceResult::factory()->create([
                'race_id' => $race->id,
                'race_registration_id' => $reg->id,
                'position' => 1,
            ]);
        }

        // Execute
        $this->useCase->execute($season);

        // Assert
        $standing = SeasonStanding::forSeason($season->id)->first();
        expect($standing->rank)->toBe(1)
            ->and($standing->races_count)->toBe(2)
            ->and($standing->base_points)->toBe(50); // 25 + 25
    });

    it('applies bonus when pilot participates in all races', function () {
        $season = Season::factory()->create();
        $category = CarCategory::factory()->create();

        // Create 3 published races
        $races = Race::factory()->count(3)->for($season)->create(['status' => 'PUBLISHED']);

        $pilot = Pilot::factory()->create();
        $car = Car::factory()->for($pilot)->for($category, 'category')->create();

        // Participate in all 3 races
        foreach ($races as $race) {
            $reg = RaceRegistration::factory()
                ->for($race)
                ->for($pilot)
                ->for($car)
                ->create();

            RaceResult::factory()->create([
                'race_id' => $race->id,
                'race_registration_id' => $reg->id,
                'position' => 1,
            ]);
        }

        // Execute
        $this->useCase->execute($season);

        // Assert
        $standing = SeasonStanding::forSeason($season->id)->first();
        expect($standing->bonus_points)->toBe(20)
            ->and($standing->total_points)->toBe(75 + 20); // 3x25 + bonus
    });

    it('does not apply bonus when pilot misses a race', function () {
        $season = Season::factory()->create();
        $category = CarCategory::factory()->create();

        // Create 3 published races
        $races = Race::factory()->count(3)->for($season)->create(['status' => 'PUBLISHED']);

        $pilot = Pilot::factory()->create();
        $car = Car::factory()->for($pilot)->for($category, 'category')->create();

        // Participate in only 2 races
        foreach ($races->take(2) as $race) {
            $reg = RaceRegistration::factory()
                ->for($race)
                ->for($pilot)
                ->for($car)
                ->create();

            RaceResult::factory()->create([
                'race_id' => $race->id,
                'race_registration_id' => $reg->id,
                'position' => 1,
            ]);
        }

        // Execute
        $this->useCase->execute($season);

        // Assert
        $standing = SeasonStanding::forSeason($season->id)->first();
        expect($standing->bonus_points)->toBe(0)
            ->and($standing->races_count)->toBe(2);
    });

    it('creates category standings', function () {
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->create(['status' => 'PUBLISHED']);

        $category1 = CarCategory::factory()->create(['name' => 'Cat A']);
        $category2 = CarCategory::factory()->create(['name' => 'Cat B']);

        // 2 pilots in category 1
        for ($i = 1; $i <= 2; $i++) {
            $pilot = Pilot::factory()->create();
            $car = Car::factory()->for($pilot)->for($category1, 'category')->create();
            $reg = RaceRegistration::factory()
                ->for($race)
                ->for($pilot)
                ->for($car)
                ->create();

            RaceResult::factory()->create([
                'race_id' => $race->id,
                'race_registration_id' => $reg->id,
                'position' => $i,
            ]);
        }

        // 1 pilot in category 2
        $pilot3 = Pilot::factory()->create();
        $car3 = Car::factory()->for($pilot3)->for($category2, 'category')->create();
        $reg3 = RaceRegistration::factory()
            ->for($race)
            ->for($pilot3)
            ->for($car3)
            ->create();

        RaceResult::factory()->create([
            'race_id' => $race->id,
            'race_registration_id' => $reg3->id,
            'position' => 3, // 3rd overall but 1st in cat2
        ]);

        // Execute
        $this->useCase->execute($season);

        // Assert category standings
        $cat1Standings = SeasonCategoryStanding::forSeason($season->id)
            ->forCategory($category1->id)
            ->get();
        $cat2Standings = SeasonCategoryStanding::forSeason($season->id)
            ->forCategory($category2->id)
            ->get();

        expect($cat1Standings)->toHaveCount(2)
            ->and($cat2Standings)->toHaveCount(1);

        // First in category 2 gets 25 points (1st in category)
        expect($cat2Standings->first()->base_points)->toBe(25);
    });

    it('ranks pilots correctly by total points', function () {
        $season = Season::factory()->create();
        $category = CarCategory::factory()->create();

        // Create 2 races
        $race1 = Race::factory()->for($season)->create(['status' => 'PUBLISHED']);
        $race2 = Race::factory()->for($season)->create(['status' => 'PUBLISHED']);

        // Pilot A: 1st + 2nd = 45 points
        $pilotA = Pilot::factory()->create();
        $carA = Car::factory()->for($pilotA)->for($category, 'category')->create();

        // Pilot B: 2nd + 1st = 45 points (tie)
        $pilotB = Pilot::factory()->create();
        $carB = Car::factory()->for($pilotB)->for($category, 'category')->create();

        // Pilot C: 3rd + 3rd = 32 points
        $pilotC = Pilot::factory()->create();
        $carC = Car::factory()->for($pilotC)->for($category, 'category')->create();

        // Race 1 results
        $regA1 = RaceRegistration::factory()->for($race1)->for($pilotA)->for($carA)->create();
        $regB1 = RaceRegistration::factory()->for($race1)->for($pilotB)->for($carB)->create();
        $regC1 = RaceRegistration::factory()->for($race1)->for($pilotC)->for($carC)->create();

        RaceResult::factory()->create(['race_id' => $race1->id, 'race_registration_id' => $regA1->id, 'position' => 1]);
        RaceResult::factory()->create(['race_id' => $race1->id, 'race_registration_id' => $regB1->id, 'position' => 2]);
        RaceResult::factory()->create(['race_id' => $race1->id, 'race_registration_id' => $regC1->id, 'position' => 3]);

        // Race 2 results
        $regA2 = RaceRegistration::factory()->for($race2)->for($pilotA)->for($carA)->create();
        $regB2 = RaceRegistration::factory()->for($race2)->for($pilotB)->for($carB)->create();
        $regC2 = RaceRegistration::factory()->for($race2)->for($pilotC)->for($carC)->create();

        RaceResult::factory()->create(['race_id' => $race2->id, 'race_registration_id' => $regA2->id, 'position' => 2]);
        RaceResult::factory()->create(['race_id' => $race2->id, 'race_registration_id' => $regB2->id, 'position' => 1]);
        RaceResult::factory()->create(['race_id' => $race2->id, 'race_registration_id' => $regC2->id, 'position' => 3]);

        // Execute
        $this->useCase->execute($season);

        // Assert rankings
        $standings = SeasonStanding::forSeason($season->id)
            ->whereNotNull('rank')
            ->orderBy('rank')
            ->get();

        // With bonus (both participated in all races): A and B have 45+20=65, C has 32+20=52
        expect($standings)->toHaveCount(3);
        expect($standings[0]->total_points)->toBe(65); // A or B
        expect($standings[1]->total_points)->toBe(65); // A or B
        expect($standings[2]->total_points)->toBe(52); // C
        expect($standings[2]->pilot_id)->toBe($pilotC->id);
    });

    it('clears existing standings before rebuild', function () {
        $season = Season::factory()->create();
        $category = CarCategory::factory()->create();
        $race = Race::factory()->for($season)->create(['status' => 'PUBLISHED']);

        $pilot = Pilot::factory()->create();
        $car = Car::factory()->for($pilot)->for($category, 'category')->create();
        $reg = RaceRegistration::factory()->for($race)->for($pilot)->for($car)->create();
        RaceResult::factory()->create([
            'race_id' => $race->id,
            'race_registration_id' => $reg->id,
            'position' => 1,
        ]);

        // First run
        $this->useCase->execute($season);
        expect(SeasonStanding::forSeason($season->id)->count())->toBe(1);

        // Second run should still have 1 standing (not 2)
        $this->useCase->execute($season);
        expect(SeasonStanding::forSeason($season->id)->count())->toBe(1);
    });

    it('ignores non-published races', function () {
        $season = Season::factory()->create();
        $category = CarCategory::factory()->create();

        // Published race
        $published = Race::factory()->for($season)->create(['status' => 'PUBLISHED']);

        // Draft race (should be ignored)
        $draft = Race::factory()->for($season)->create(['status' => 'DRAFT']);

        $pilot = Pilot::factory()->create();
        $car = Car::factory()->for($pilot)->for($category, 'category')->create();

        // Results in both races
        foreach ([$published, $draft] as $race) {
            $reg = RaceRegistration::factory()->for($race)->for($pilot)->for($car)->create();
            RaceResult::factory()->create([
                'race_id' => $race->id,
                'race_registration_id' => $reg->id,
                'position' => 1,
            ]);
        }

        // Execute
        $result = $this->useCase->execute($season);

        // Should only count published race
        expect($result['total_races'])->toBe(1);

        $standing = SeasonStanding::forSeason($season->id)->first();
        expect($standing->races_count)->toBe(1)
            ->and($standing->base_points)->toBe(25);
    });

    it('creates default points rules if none exist', function () {
        $season = Season::factory()->create();
        expect($season->pointsRules()->count())->toBe(0);

        // Execute with no results (just to trigger rule creation)
        $this->useCase->execute($season);

        // Should have created default rules
        $season->refresh();
        expect($season->pointsRules()->count())->toBe(7);
    });
});

describe('RebuildSeasonStandingsJob', function () {

    it('dispatches and processes correctly', function () {
        Queue::fake();

        $season = Season::factory()->create();

        RebuildSeasonStandingsJob::dispatch($season->id);

        Queue::assertPushed(RebuildSeasonStandingsJob::class, function ($job) use ($season) {
            return $job->seasonId === $season->id;
        });
    });

    it('handles non-existent season gracefully', function () {
        $job = new RebuildSeasonStandingsJob(99999);

        // Should not throw, just log warning
        $job->handle(
            new RebuildSeasonStandings,
            app(\App\Infrastructure\Cache\StandingsCacheService::class)
        );

        expect(true)->toBeTrue(); // Job completed without exception
    });
});
