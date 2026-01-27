<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Infrastructure\Import\ResultsCsvImporter;
use App\Models\Race;
use App\Models\ResultImport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Facades\Activity;

/**
 * Job for importing race results from a CSV file asynchronously.
 * Used for large files (>1000 rows) to avoid request timeout.
 */
class ImportRaceResultsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Race $race,
        public ResultImport $import,
        public User $uploader,
    ) {
        $this->onQueue('imports');
    }

    /**
     * Execute the job.
     */
    public function handle(ResultsCsvImporter $importer): void
    {
        Log::info("Starting async import for race {$this->race->id}", [
            'import_id' => $this->import->id,
            'file' => $this->import->original_filename,
        ]);

        try {
            // Mark as processing
            $this->import->update(['status' => 'PROCESSING']);

            // Process the import
            $success = $importer->import($this->race, $this->import);

            // Log activity
            Activity::causedBy($this->uploader)
                ->performedOn($this->race)
                ->withProperties([
                    'import_id' => $this->import->id,
                    'filename' => $this->import->original_filename,
                    'success' => $success,
                    'row_count' => $this->import->row_count,
                    'async' => true,
                ])
                ->log($success ? 'import_results_success_async' : 'import_results_failed_async');

            Log::info("Async import completed for race {$this->race->id}", [
                'import_id' => $this->import->id,
                'success' => $success,
                'row_count' => $this->import->row_count,
            ]);
        } catch (\Exception $e) {
            Log::error("Async import failed for race {$this->race->id}", [
                'import_id' => $this->import->id,
                'error' => $e->getMessage(),
            ]);

            $this->import->update([
                'status' => 'FAILED',
                'errors' => [['row' => 0, 'message' => 'Erreur système: '.$e->getMessage()]],
            ]);

            throw $e;
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Import job failed after all retries', [
            'race_id' => $this->race->id,
            'import_id' => $this->import->id,
            'error' => $exception->getMessage(),
        ]);

        $this->import->update([
            'status' => 'FAILED',
            'errors' => [['row' => 0, 'message' => 'Import échoué après plusieurs tentatives: '.$exception->getMessage()]],
        ]);
    }
}
