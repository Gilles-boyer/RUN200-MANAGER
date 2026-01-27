<?php

declare(strict_types=1);

use App\Application\Results\UseCases\ImportRaceResults;
use App\Application\Results\UseCases\PublishRaceResults;
use App\Infrastructure\Import\ResultsCsvImporter;
use App\Models\Car;
use App\Models\CarCategory;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\RaceResult;
use App\Models\ResultImport;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

describe('ResultsCsvImporter', function () {

    it('parses valid CSV and creates results', function () {
        // Setup
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->closed()->create();
        $category = CarCategory::factory()->create(['name' => 'Berline']);

        // Create 3 registrations with cars
        $registrations = collect();
        for ($i = 1; $i <= 3; $i++) {
            $pilot = Pilot::factory()->create();
            $car = Car::factory()->for($pilot)->for($category, 'category')->create(['race_number' => $i * 10]);
            $registration = RaceRegistration::factory()
                ->for($race)
                ->for($pilot)
                ->for($car)
                ->create(['status' => 'CONFIRMED']);
            $registrations->push($registration);
        }

        // Create CSV content
        $csv = "position,bib,pilote,voiture,catégorie,temps\n";
        $csv .= "1,10,{$registrations[0]->pilot->full_name},{$registrations[0]->car->model},Berline,2:34.567\n";
        $csv .= "2,20,{$registrations[1]->pilot->full_name},{$registrations[1]->car->model},Berline,2:45.123\n";
        $csv .= "3,30,{$registrations[2]->pilot->full_name},{$registrations[2]->car->model},Berline,2:56.789\n";

        $path = 'imports/test.csv';
        Storage::put($path, $csv);

        $import = ResultImport::factory()
            ->forRace($race)
            ->create(['stored_path' => $path, 'status' => 'PENDING']);

        // Execute
        $importer = new ResultsCsvImporter;
        $result = $importer->import($race, $import);

        // Assert
        expect($result)->toBeTrue();

        $import->refresh();
        expect($import->status)->toBe('IMPORTED')
            ->and($import->row_count)->toBe(3)
            ->and($import->errors)->toBeNull();

        $race->refresh();
        expect($race->status)->toBe('RESULTS_READY')
            ->and($race->results)->toHaveCount(3);

        $firstResult = $race->results()->where('position', 1)->first();
        expect($firstResult->bib)->toBe(10)
            ->and($firstResult->time_ms)->toBe(154567); // 2:34.567 = 154567ms
    });

    it('handles semicolon delimiter', function () {
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->closed()->create();
        $pilot = Pilot::factory()->create();
        $car = Car::factory()->for($pilot)->create(['race_number' => 42]);
        RaceRegistration::factory()->for($race)->for($pilot)->for($car)->create(['status' => 'CONFIRMED']);

        $csv = "position;bib;pilote;voiture;catégorie;temps\n";
        $csv .= "1;42;John Doe;Test Car;Sport;1:23.456\n";

        $path = 'imports/test.csv';
        Storage::put($path, $csv);

        $import = ResultImport::factory()->forRace($race)->create(['stored_path' => $path]);

        $importer = new ResultsCsvImporter;
        $result = $importer->import($race, $import);

        expect($result)->toBeTrue();
        expect($race->results()->count())->toBe(1);
    });

    it('handles alternative column names', function () {
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->closed()->create();
        $pilot = Pilot::factory()->create();
        $car = Car::factory()->for($pilot)->create(['race_number' => 99]);
        RaceRegistration::factory()->for($race)->for($pilot)->for($car)->create(['status' => 'CONFIRMED']);

        $csv = "pos,dossard,driver,vehicle,class,time\n";
        $csv .= "1,99,Jane Doe,Fast Car,Racing,3:00.000\n";

        $path = 'imports/test.csv';
        Storage::put($path, $csv);

        $import = ResultImport::factory()->forRace($race)->create(['stored_path' => $path]);

        $importer = new ResultsCsvImporter;
        $result = $importer->import($race, $import);

        expect($result)->toBeTrue();
    });

    it('fails on duplicate position', function () {
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->closed()->create();

        $pilot1 = Pilot::factory()->create();
        $car1 = Car::factory()->for($pilot1)->create(['race_number' => 10]);
        RaceRegistration::factory()->for($race)->for($pilot1)->for($car1)->create(['status' => 'CONFIRMED']);

        $pilot2 = Pilot::factory()->create();
        $car2 = Car::factory()->for($pilot2)->create(['race_number' => 20]);
        RaceRegistration::factory()->for($race)->for($pilot2)->for($car2)->create(['status' => 'CONFIRMED']);

        $csv = "position,bib,pilote,voiture,catégorie,temps\n";
        $csv .= "1,10,Pilot A,Car A,Cat,2:00.000\n";
        $csv .= "1,20,Pilot B,Car B,Cat,2:01.000\n"; // Duplicate position!

        $path = 'imports/test.csv';
        Storage::put($path, $csv);

        $import = ResultImport::factory()->forRace($race)->create(['stored_path' => $path]);

        $importer = new ResultsCsvImporter;
        $result = $importer->import($race, $import);

        expect($result)->toBeFalse();

        $import->refresh();
        expect($import->status)->toBe('FAILED')
            ->and($import->errors)->toBeArray()
            ->and($import->errors)->toHaveCount(1);
    });

    it('fails on duplicate bib', function () {
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->closed()->create();

        $pilot = Pilot::factory()->create();
        $car = Car::factory()->for($pilot)->create(['race_number' => 10]);
        RaceRegistration::factory()->for($race)->for($pilot)->for($car)->create(['status' => 'CONFIRMED']);

        $csv = "position,bib,pilote,voiture,catégorie,temps\n";
        $csv .= "1,10,Pilot A,Car A,Cat,2:00.000\n";
        $csv .= "2,10,Pilot A,Car A,Cat,2:01.000\n"; // Same bib!

        $path = 'imports/test.csv';
        Storage::put($path, $csv);

        $import = ResultImport::factory()->forRace($race)->create(['stored_path' => $path]);

        $importer = new ResultsCsvImporter;
        $result = $importer->import($race, $import);

        expect($result)->toBeFalse();

        $import->refresh();
        expect($import->status)->toBe('FAILED');
    });

    it('fails on unregistered bib', function () {
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->closed()->create();

        $csv = "position,bib,pilote,voiture,catégorie,temps\n";
        $csv .= "1,999,Unknown,Unknown,Cat,2:00.000\n"; // Bib 999 not registered

        $path = 'imports/test.csv';
        Storage::put($path, $csv);

        $import = ResultImport::factory()->forRace($race)->create(['stored_path' => $path]);

        $importer = new ResultsCsvImporter;
        $result = $importer->import($race, $import);

        expect($result)->toBeFalse();

        $import->refresh();
        expect($import->status)->toBe('FAILED')
            ->and($import->errors[0]['message'])->toContain('999');
    });

    it('fails on invalid time format', function () {
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->closed()->create();

        $pilot = Pilot::factory()->create();
        $car = Car::factory()->for($pilot)->create(['race_number' => 10]);
        RaceRegistration::factory()->for($race)->for($pilot)->for($car)->create(['status' => 'CONFIRMED']);

        $csv = "position,bib,pilote,voiture,catégorie,temps\n";
        $csv .= "1,10,Pilot,Car,Cat,invalid_time\n";

        $path = 'imports/test.csv';
        Storage::put($path, $csv);

        $import = ResultImport::factory()->forRace($race)->create(['stored_path' => $path]);

        $importer = new ResultsCsvImporter;
        $result = $importer->import($race, $import);

        expect($result)->toBeFalse();

        $import->refresh();
        expect($import->status)->toBe('FAILED')
            ->and($import->errors[0]['message'])->toContain('temps invalide');
    });

    it('parses various time formats correctly', function () {
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->closed()->create();

        // Create 4 registrations
        for ($i = 1; $i <= 4; $i++) {
            $pilot = Pilot::factory()->create();
            $car = Car::factory()->for($pilot)->create(['race_number' => $i]);
            RaceRegistration::factory()->for($race)->for($pilot)->for($car)->create(['status' => 'CONFIRMED']);
        }

        // Use semicolon delimiter to allow comma in time values
        $csv = "position;bib;pilote;voiture;catégorie;temps\n";
        $csv .= "1;1;P1;C1;Cat;2:34.567\n";     // MM:SS.mmm
        $csv .= "2;2;P2;C2;Cat;2:34,567\n";     // MM:SS,mmm (comma decimal)
        $csv .= "3;3;P3;C3;Cat;1:02:34.567\n";  // HH:MM:SS.mmm
        $csv .= "4;4;P4;C4;Cat;154.567\n";      // SS.mmm

        $path = 'imports/test.csv';
        Storage::put($path, $csv);

        $import = ResultImport::factory()->forRace($race)->create(['stored_path' => $path]);

        $importer = new ResultsCsvImporter;
        $result = $importer->import($race, $import);

        expect($result)->toBeTrue();

        $results = $race->results()->orderBy('position')->get();
        expect($results[0]->time_ms)->toBe(154567)    // 2:34.567
            ->and($results[1]->time_ms)->toBe(154567)  // 2:34,567
            ->and($results[2]->time_ms)->toBe(3754567) // 1:02:34.567
            ->and($results[3]->time_ms)->toBe(154567); // 154.567
    });

    it('replaces existing results on reimport', function () {
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->closed()->create();

        $pilot = Pilot::factory()->create();
        $car = Car::factory()->for($pilot)->create(['race_number' => 10]);
        RaceRegistration::factory()->for($race)->for($pilot)->for($car)->create(['status' => 'CONFIRMED']);

        // Create existing result
        RaceResult::factory()->forRace($race)->create(['position' => 1, 'bib' => 10]);

        expect($race->results()->count())->toBe(1);

        // Import new results
        $csv = "position,bib,pilote,voiture,catégorie,temps\n";
        $csv .= "1,10,New Pilot,New Car,Cat,3:00.000\n";

        $path = 'imports/test.csv';
        Storage::put($path, $csv);

        $import = ResultImport::factory()->forRace($race)->create(['stored_path' => $path]);

        $importer = new ResultsCsvImporter;
        $result = $importer->import($race, $import);

        expect($result)->toBeTrue();
        expect($race->results()->count())->toBe(1); // Still 1, not 2
    });

});

describe('ImportRaceResults Use Case', function () {

    it('imports results from uploaded file', function () {
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->closed()->create();
        $user = User::factory()->create();

        $pilot = Pilot::factory()->create();
        $car = Car::factory()->for($pilot)->create(['race_number' => 42]);
        RaceRegistration::factory()->for($race)->for($pilot)->for($car)->create(['status' => 'CONFIRMED']);

        $csv = "position,bib,pilote,voiture,catégorie,temps\n";
        $csv .= "1,42,Test Pilot,Test Car,Cat,2:00.000\n";

        $file = UploadedFile::fake()->createWithContent('results.csv', $csv);

        $useCase = app(ImportRaceResults::class);
        $import = $useCase->execute($race, $file, $user);

        expect($import->status)->toBe('IMPORTED')
            ->and($import->uploaded_by)->toBe($user->id)
            ->and($import->original_filename)->toBe('results.csv');

        $race->refresh();
        expect($race->status)->toBe('RESULTS_READY');
    });

    it('throws exception for invalid race status', function () {
        $race = Race::factory()->published()->create();
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('results.csv', 'test');

        $useCase = app(ImportRaceResults::class);

        expect(fn () => $useCase->execute($race, $file, $user))
            ->toThrow(\InvalidArgumentException::class);
    });

});

describe('PublishRaceResults Use Case', function () {

    it('publishes race results', function () {
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->resultsReady()->create();
        $user = User::factory()->create();

        // Add some results
        RaceResult::factory()->forRace($race)->count(5)->create();

        $useCase = new PublishRaceResults;
        $result = $useCase->execute($race, $user);

        expect($result->status)->toBe('PUBLISHED');
    });

    it('throws exception when no results exist', function () {
        $race = Race::factory()->resultsReady()->create();
        $user = User::factory()->create();

        $useCase = new PublishRaceResults;

        expect(fn () => $useCase->execute($race, $user))
            ->toThrow(\InvalidArgumentException::class, 'pas de résultats');
    });

    it('throws exception for invalid race status', function () {
        $race = Race::factory()->closed()->create();
        $user = User::factory()->create();

        $useCase = new PublishRaceResults;

        expect(fn () => $useCase->execute($race, $user))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('unpublishes results', function () {
        $season = Season::factory()->create();
        $race = Race::factory()->for($season)->published()->create();
        $user = User::factory()->create();

        RaceResult::factory()->forRace($race)->count(3)->create();

        $useCase = new PublishRaceResults;
        $result = $useCase->unpublish($race, $user);

        expect($result->status)->toBe('RESULTS_READY');
    });

});
