<?php

declare(strict_types=1);

use App\Models\CarCategory;
use App\Models\Pilot;
use App\Models\Season;
use App\Models\SeasonCategoryStanding;
use App\Models\SeasonPointsRule;
use App\Models\SeasonStanding;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('SeasonPointsRule Model', function () {

    it('can be created with valid data', function () {
        $season = Season::factory()->create();

        $rule = SeasonPointsRule::create([
            'season_id' => $season->id,
            'position_from' => 1,
            'position_to' => 1,
            'points' => 25,
        ]);

        expect($rule)->toBeInstanceOf(SeasonPointsRule::class)
            ->and($rule->position_from)->toBe(1)
            ->and($rule->position_to)->toBe(1)
            ->and($rule->points)->toBe(25);
    });

    it('belongs to a season', function () {
        $season = Season::factory()->create();
        $rule = SeasonPointsRule::factory()->create(['season_id' => $season->id]);

        expect($rule->season)->toBeInstanceOf(Season::class)
            ->and($rule->season->id)->toBe($season->id);
    });

    it('correctly checks if position is covered', function () {
        $rule = SeasonPointsRule::factory()->create([
            'position_from' => 7,
            'position_to' => 9999,
            'points' => 5,
        ]);

        expect($rule->coversPosition(7))->toBeTrue()
            ->and($rule->coversPosition(100))->toBeTrue()
            ->and($rule->coversPosition(6))->toBeFalse();
    });

    it('can be scoped by season', function () {
        $season1 = Season::factory()->create();
        $season2 = Season::factory()->create();

        SeasonPointsRule::factory()->count(3)->create(['season_id' => $season1->id]);
        SeasonPointsRule::factory()->count(2)->create(['season_id' => $season2->id]);

        expect(SeasonPointsRule::forSeason($season1->id)->count())->toBe(3)
            ->and(SeasonPointsRule::forSeason($season2->id)->count())->toBe(2);
    });
});

describe('SeasonStanding Model', function () {

    it('can be created with valid data', function () {
        $season = Season::factory()->create();
        $pilot = Pilot::factory()->create();

        $standing = SeasonStanding::create([
            'season_id' => $season->id,
            'pilot_id' => $pilot->id,
            'races_count' => 3,
            'base_points' => 61, // 25 + 20 + 16
            'bonus_points' => 20,
            'total_points' => 81,
            'rank' => 1,
            'computed_at' => now(),
        ]);

        expect($standing)->toBeInstanceOf(SeasonStanding::class)
            ->and($standing->total_points)->toBe(81)
            ->and($standing->rank)->toBe(1);
    });

    it('belongs to season and pilot', function () {
        $standing = SeasonStanding::factory()->create();

        expect($standing->season)->toBeInstanceOf(Season::class)
            ->and($standing->pilot)->toBeInstanceOf(Pilot::class);
    });

    it('checks eligibility for ranking', function () {
        $standing1 = SeasonStanding::factory()->create(['races_count' => 1]);
        $standing2 = SeasonStanding::factory()->create(['races_count' => 2]);
        $standing3 = SeasonStanding::factory()->create(['races_count' => 5]);

        expect($standing1->isEligibleForRanking())->toBeFalse()
            ->and($standing2->isEligibleForRanking())->toBeTrue()
            ->and($standing3->isEligibleForRanking())->toBeTrue();
    });

    it('checks bonus status', function () {
        $standingWithBonus = SeasonStanding::factory()->create(['bonus_points' => 20]);
        $standingWithoutBonus = SeasonStanding::factory()->create(['bonus_points' => 0]);

        expect($standingWithBonus->hasBonus())->toBeTrue()
            ->and($standingWithoutBonus->hasBonus())->toBeFalse();
    });

    it('returns correct rank display', function () {
        $ranked = SeasonStanding::factory()->create(['races_count' => 3, 'rank' => 1]);
        $unranked = SeasonStanding::factory()->create(['races_count' => 1, 'rank' => null]);

        expect($ranked->rank_display)->toBe('1')
            ->and($unranked->rank_display)->toBe('NC');
    });

    it('can scope by ranked and unranked', function () {
        $season = Season::factory()->create();
        SeasonStanding::factory()->ranked(1)->create(['season_id' => $season->id]);
        SeasonStanding::factory()->ranked(2)->create(['season_id' => $season->id]);
        SeasonStanding::factory()->unranked()->create(['season_id' => $season->id]);

        expect(SeasonStanding::forSeason($season->id)->ranked()->count())->toBe(2)
            ->and(SeasonStanding::forSeason($season->id)->unranked()->count())->toBe(1);
    });

    it('enforces unique constraint on season and pilot', function () {
        $season = Season::factory()->create();
        $pilot = Pilot::factory()->create();

        SeasonStanding::factory()->create([
            'season_id' => $season->id,
            'pilot_id' => $pilot->id,
        ]);

        expect(fn () => SeasonStanding::factory()->create([
            'season_id' => $season->id,
            'pilot_id' => $pilot->id,
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });
});

describe('SeasonCategoryStanding Model', function () {

    it('can be created with valid data', function () {
        $season = Season::factory()->create();
        $pilot = Pilot::factory()->create();
        $category = CarCategory::factory()->create();

        $standing = SeasonCategoryStanding::create([
            'season_id' => $season->id,
            'car_category_id' => $category->id,
            'pilot_id' => $pilot->id,
            'races_count' => 2,
            'base_points' => 45,
            'bonus_points' => 0,
            'total_points' => 45,
            'rank' => 2,
            'computed_at' => now(),
        ]);

        expect($standing)->toBeInstanceOf(SeasonCategoryStanding::class)
            ->and($standing->total_points)->toBe(45);
    });

    it('belongs to season, category and pilot', function () {
        $standing = SeasonCategoryStanding::factory()->create();

        expect($standing->season)->toBeInstanceOf(Season::class)
            ->and($standing->category)->toBeInstanceOf(CarCategory::class)
            ->and($standing->pilot)->toBeInstanceOf(Pilot::class);
    });

    it('can be scoped by category', function () {
        $season = Season::factory()->create();
        $cat1 = CarCategory::factory()->create();
        $cat2 = CarCategory::factory()->create();

        SeasonCategoryStanding::factory()->count(3)->create([
            'season_id' => $season->id,
            'car_category_id' => $cat1->id,
        ]);
        SeasonCategoryStanding::factory()->count(2)->create([
            'season_id' => $season->id,
            'car_category_id' => $cat2->id,
        ]);

        expect(SeasonCategoryStanding::forSeason($season->id)->forCategory($cat1->id)->count())->toBe(3)
            ->and(SeasonCategoryStanding::forSeason($season->id)->forCategory($cat2->id)->count())->toBe(2);
    });

    it('enforces unique constraint on season, category and pilot', function () {
        $season = Season::factory()->create();
        $pilot = Pilot::factory()->create();
        $category = CarCategory::factory()->create();

        SeasonCategoryStanding::factory()->create([
            'season_id' => $season->id,
            'car_category_id' => $category->id,
            'pilot_id' => $pilot->id,
        ]);

        expect(fn () => SeasonCategoryStanding::factory()->create([
            'season_id' => $season->id,
            'car_category_id' => $category->id,
            'pilot_id' => $pilot->id,
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });
});
