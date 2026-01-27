<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\ImportRaceResultsJob;
use App\Models\Race;
use App\Models\ResultImport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportRaceResultsJobTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Race $race;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->race = Race::factory()->create(['status' => 'CLOSED']);

        Storage::fake('local');
    }

    public function test_job_can_be_dispatched_to_imports_queue(): void
    {
        Queue::fake();

        $import = ResultImport::create([
            'race_id' => $this->race->id,
            'uploaded_by' => $this->admin->id,
            'original_filename' => 'test.csv',
            'stored_path' => 'imports/test.csv',
            'row_count' => 0,
            'status' => 'PENDING',
        ]);

        ImportRaceResultsJob::dispatch($this->race, $import, $this->admin);

        Queue::assertPushedOn('imports', ImportRaceResultsJob::class);
    }

    public function test_job_contains_correct_data(): void
    {
        Queue::fake();

        $import = ResultImport::create([
            'race_id' => $this->race->id,
            'uploaded_by' => $this->admin->id,
            'original_filename' => 'results.csv',
            'stored_path' => 'imports/results.csv',
            'row_count' => 0,
            'status' => 'PENDING',
        ]);

        ImportRaceResultsJob::dispatch($this->race, $import, $this->admin);

        Queue::assertPushed(ImportRaceResultsJob::class, function ($job) use ($import) {
            return $job->race->id === $this->race->id
                && $job->import->id === $import->id
                && $job->uploader->id === $this->admin->id;
        });
    }

    public function test_job_has_correct_configuration(): void
    {
        $import = ResultImport::create([
            'race_id' => $this->race->id,
            'uploaded_by' => $this->admin->id,
            'original_filename' => 'test.csv',
            'stored_path' => 'imports/test.csv',
            'row_count' => 0,
            'status' => 'PENDING',
        ]);

        $job = new ImportRaceResultsJob($this->race, $import, $this->admin);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(300, $job->timeout);
        $this->assertEquals('imports', $job->queue);
    }
}
