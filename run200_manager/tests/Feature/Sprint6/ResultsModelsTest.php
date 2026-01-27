<?php

declare(strict_types=1);

use App\Models\Race;
use App\Models\RaceResult;
use App\Models\ResultImport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('RaceResult Model', function () {

    it('has correct fillable attributes', function () {
        $result = new RaceResult;

        expect($result->getFillable())->toContain(
            'race_id',
            'race_registration_id',
            'result_import_id',
            'position',
            'bib',
            'raw_time',
            'time_ms',
            'pilot_name',
            'car_description',
            'category_name'
        );
    });

    it('belongs to a race', function () {
        $race = Race::factory()->create();
        $result = RaceResult::factory()->forRace($race)->create();

        expect($result->race->id)->toBe($race->id);
    });

    it('formats time correctly', function () {
        $result = RaceResult::factory()->create(['time_ms' => 154567]); // 2:34.567

        expect($result->formatted_time)->toBe('2:34.567');
    });

    it('formats time with hours correctly', function () {
        $result = RaceResult::factory()->create(['time_ms' => 3754567]); // 1:02:34.567

        expect($result->formatted_time)->toBe('1:02:34.567');
    });

    it('calculates points for position', function () {
        $result = RaceResult::factory()->winner()->create();

        expect($result->getPoints())->toBe(25); // First place

        $result2 = RaceResult::factory()->position(2)->create();
        expect($result2->getPoints())->toBe(18);

        $result3 = RaceResult::factory()->position(3)->create();
        expect($result3->getPoints())->toBe(15);

        $result10 = RaceResult::factory()->position(10)->create();
        expect($result10->getPoints())->toBe(1);

        $result11 = RaceResult::factory()->position(11)->create();
        expect($result11->getPoints())->toBe(0);
    });

    it('has podium scope', function () {
        $race = Race::factory()->create();

        RaceResult::factory()->forRace($race)->position(1)->create();
        RaceResult::factory()->forRace($race)->position(2)->create();
        RaceResult::factory()->forRace($race)->position(3)->create();
        RaceResult::factory()->forRace($race)->position(4)->create();
        RaceResult::factory()->forRace($race)->position(5)->create();

        $podium = RaceResult::where('race_id', $race->id)->podium()->get();

        expect($podium)->toHaveCount(3);
        expect($podium->pluck('position')->toArray())->toBe([1, 2, 3]);
    });

});

describe('ResultImport Model', function () {

    it('has correct fillable attributes', function () {
        $import = new ResultImport;

        expect($import->getFillable())->toContain(
            'race_id',
            'uploaded_by',
            'original_filename',
            'stored_path',
            'row_count',
            'status',
            'errors'
        );
    });

    it('belongs to a race', function () {
        $race = Race::factory()->create();
        $import = ResultImport::factory()->forRace($race)->create();

        expect($import->race->id)->toBe($race->id);
    });

    it('belongs to an uploader', function () {
        $user = User::factory()->create();
        $import = ResultImport::factory()->uploadedBy($user)->create();

        expect($import->uploader->id)->toBe($user->id);
    });

    it('has status helper methods', function () {
        $pending = ResultImport::factory()->pending()->create();
        expect($pending->isPending())->toBeTrue()
            ->and($pending->isImported())->toBeFalse()
            ->and($pending->isFailed())->toBeFalse();

        $imported = ResultImport::factory()->imported()->create();
        expect($imported->isPending())->toBeFalse()
            ->and($imported->isImported())->toBeTrue()
            ->and($imported->isFailed())->toBeFalse();

        $failed = ResultImport::factory()->failed()->create();
        expect($failed->isPending())->toBeFalse()
            ->and($failed->isImported())->toBeFalse()
            ->and($failed->isFailed())->toBeTrue();
    });

    it('casts errors to array', function () {
        $errors = [
            ['row' => 2, 'message' => 'Error 1'],
            ['row' => 5, 'message' => 'Error 2'],
        ];

        $import = ResultImport::factory()->failed($errors)->create();

        expect($import->errors)->toBeArray()
            ->and($import->errors)->toHaveCount(2)
            ->and($import->errors[0]['row'])->toBe(2);
    });

});

describe('Race Model Results Relations', function () {

    it('has results relationship', function () {
        $race = Race::factory()->create();
        RaceResult::factory()->forRace($race)->count(5)->create();

        expect($race->results)->toHaveCount(5);
    });

    it('has resultImports relationship', function () {
        $race = Race::factory()->create();
        ResultImport::factory()->forRace($race)->count(3)->create();

        expect($race->resultImports)->toHaveCount(3);
    });

    it('has latestImport relationship', function () {
        $race = Race::factory()->create();

        $older = ResultImport::factory()->forRace($race)->create(['created_at' => now()->subHour()]);
        $newest = ResultImport::factory()->forRace($race)->create(['created_at' => now()]);

        expect($race->latestImport->id)->toBe($newest->id);
    });

    it('checks canImportResults status', function () {
        $closedRace = Race::factory()->closed()->create();
        expect($closedRace->canImportResults())->toBeTrue();

        $runningRace = Race::factory()->running()->create();
        expect($runningRace->canImportResults())->toBeTrue();

        $readyRace = Race::factory()->resultsReady()->create();
        expect($readyRace->canImportResults())->toBeTrue();

        $publishedRace = Race::factory()->published()->create();
        expect($publishedRace->canImportResults())->toBeFalse();

        $openRace = Race::factory()->open()->create();
        expect($openRace->canImportResults())->toBeFalse();
    });

    it('checks canPublishResults status', function () {
        $readyRace = Race::factory()->resultsReady()->create();
        expect($readyRace->canPublishResults())->toBeTrue();

        $closedRace = Race::factory()->closed()->create();
        expect($closedRace->canPublishResults())->toBeFalse();

        $publishedRace = Race::factory()->published()->create();
        expect($publishedRace->canPublishResults())->toBeFalse();
    });

    it('checks isPublished status', function () {
        $publishedRace = Race::factory()->published()->create();
        expect($publishedRace->isPublished())->toBeTrue();

        $readyRace = Race::factory()->resultsReady()->create();
        expect($readyRace->isPublished())->toBeFalse();
    });

    it('checks isResultsReady status', function () {
        $readyRace = Race::factory()->resultsReady()->create();
        expect($readyRace->isResultsReady())->toBeTrue();

        $closedRace = Race::factory()->closed()->create();
        expect($closedRace->isResultsReady())->toBeFalse();
    });

});
