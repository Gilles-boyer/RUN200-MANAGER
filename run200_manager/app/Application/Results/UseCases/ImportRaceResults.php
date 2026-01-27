<?php

declare(strict_types=1);

namespace App\Application\Results\UseCases;

use App\Infrastructure\Import\ResultsCsvImporter;
use App\Models\Race;
use App\Models\ResultImport;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Spatie\Activitylog\Facades\Activity;

/**
 * Use case for importing race results from a CSV file.
 */
final class ImportRaceResults
{
    public function __construct(
        private readonly ResultsCsvImporter $importer,
    ) {}

    /**
     * Execute the import process.
     *
     * @throws \InvalidArgumentException If the race cannot accept results import
     */
    public function execute(Race $race, UploadedFile $file, User $uploader): ResultImport
    {
        // Validate race can accept results
        if (! $race->canImportResults()) {
            throw new \InvalidArgumentException(
                "Les résultats ne peuvent être importés que pour une course fermée, en cours ou avec résultats prêts. Statut actuel: {$race->status}"
            );
        }

        // Store the uploaded file in private storage with hashed name
        $hashedName = hash('sha256', $file->getClientOriginalName().time().random_bytes(16));
        $extension = $file->getClientOriginalExtension() ?: 'csv';
        $storedPath = $file->storeAs('imports', "{$hashedName}.{$extension}", 'local');

        // Create import record
        $import = ResultImport::create([
            'race_id' => $race->id,
            'uploaded_by' => $uploader->id,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'row_count' => 0,
            'status' => 'PENDING',
            'errors' => null,
        ]);

        // Process the import
        $success = $this->importer->import($race, $import);

        // Log the activity
        $this->logActivity($race, $import, $uploader, $success);

        // Refresh the import to get updated status
        $import->refresh();

        return $import;
    }

    /**
     * Re-import results from an existing import record.
     */
    public function reimport(ResultImport $import, User $user): ResultImport
    {
        /** @var Race|null $race */
        $race = $import->race;

        if (! $race || ! $race->canImportResults()) {
            throw new \InvalidArgumentException(
                'Les résultats ne peuvent être ré-importés que pour une course fermée, en cours ou avec résultats prêts.'
            );
        }

        // Reset import status
        $import->update([
            'status' => 'PENDING',
            'errors' => null,
        ]);

        // Process the import again
        $success = $this->importer->import($race, $import);

        // Log the activity
        $this->logActivity($race, $import, $user, $success, true);

        $import->refresh();

        return $import;
    }

    /**
     * Log import activity.
     */
    private function logActivity(Race $race, ResultImport $import, User $user, bool $success, bool $isReimport = false): void
    {
        $action = $isReimport ? 'reimported' : 'imported';
        $status = $success ? 'success' : 'failed';

        Activity::causedBy($user)
            ->performedOn($import)
            ->withProperties([
                'race_id' => $race->id,
                'race_name' => $race->name,
                'filename' => $import->original_filename,
                'status' => $import->status,
                'row_count' => $import->row_count,
                'errors_count' => is_array($import->errors) ? count($import->errors) : 0,
            ])
            ->log("results_{$action}_{$status}");
    }
}
