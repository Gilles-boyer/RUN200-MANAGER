<?php

declare(strict_types=1);

namespace App\Infrastructure\Import;

use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\RaceResult;
use App\Models\ResultImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Service for importing race results from CSV files.
 *
 * CSV format expected: position, bib, pilote, voiture, catégorie, temps
 */
final class ResultsCsvImporter
{
    private const EXPECTED_COLUMNS = ['position', 'bib', 'pilote', 'voiture', 'catégorie', 'temps'];

    private const ALTERNATIVE_COLUMNS = [
        'position' => ['position', 'pos', 'place', 'rang'],
        'bib' => ['bib', 'dossard', 'numero', 'number', 'race_number'],
        'pilote' => ['pilote', 'pilot', 'driver', 'nom', 'name'],
        'voiture' => ['voiture', 'car', 'vehicule', 'vehicle'],
        'catégorie' => ['catégorie', 'categorie', 'category', 'cat', 'classe', 'class'],
        'temps' => ['temps', 'time', 'chrono', 'duree', 'duration'],
    ];

    private array $errors = [];

    private array $parsedRows = [];

    private array $columnMapping = [];

    /**
     * Import results from a CSV file for a given race.
     */
    public function import(Race $race, ResultImport $import): bool
    {
        $this->errors = [];
        $this->parsedRows = [];

        // Read and parse CSV
        $content = Storage::get($import->stored_path);
        if ($content === null) {
            $this->errors[] = ['row' => 0, 'message' => 'Fichier CSV introuvable.'];

            return $this->fail($import);
        }

        $lines = $this->parseCsvLines($content);
        if (count($lines) < 2) {
            $this->errors[] = ['row' => 0, 'message' => 'Le fichier CSV est vide ou ne contient que l\'en-tête.'];

            return $this->fail($import);
        }

        // Parse header
        $header = $lines[0];
        if (! $this->parseHeader($header)) {
            return $this->fail($import);
        }

        // Parse data rows
        $dataRows = array_slice($lines, 1);
        $this->parseDataRows($dataRows, $race);

        // Check for errors
        if (! empty($this->errors)) {
            return $this->fail($import);
        }

        // All validations passed, persist results
        return $this->persistResults($race, $import);
    }

    /**
     * Parse CSV content into lines and columns.
     */
    private function parseCsvLines(string $content): array
    {
        $lines = [];
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        foreach (explode("\n", $content) as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Detect delimiter (comma or semicolon)
            $delimiter = str_contains($line, ';') ? ';' : ',';
            $columns = str_getcsv($line, $delimiter);
            $columns = array_map('trim', $columns);

            $lines[] = $columns;
        }

        return $lines;
    }

    /**
     * Parse and validate the header row.
     */
    private function parseHeader(array $header): bool
    {
        $this->columnMapping = [];
        $normalizedHeader = array_map(fn ($col) => $this->normalizeColumnName($col), $header);

        foreach (self::ALTERNATIVE_COLUMNS as $standardName => $alternatives) {
            $found = false;
            foreach ($alternatives as $alt) {
                $index = array_search($alt, $normalizedHeader, true);
                if ($index !== false) {
                    $this->columnMapping[$standardName] = $index;
                    $found = true;
                    break;
                }
            }

            if (! $found && in_array($standardName, ['position', 'bib', 'temps'])) {
                $this->errors[] = [
                    'row' => 1,
                    'message' => "Colonne obligatoire manquante: {$standardName}",
                ];
            }
        }

        return empty($this->errors);
    }

    /**
     * Normalize a column name for matching.
     */
    private function normalizeColumnName(string $name): string
    {
        $name = mb_strtolower($name);
        $name = preg_replace('/[^a-z0-9]/', '', $name);

        return $name;
    }

    /**
     * Parse all data rows.
     */
    private function parseDataRows(array $rows, Race $race): void
    {
        $usedPositions = [];
        $usedBibs = [];

        // Pre-load registered bibs for this race
        $registeredBibs = $this->getRegisteredBibs($race);

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because of header and 1-based indexing
            $parsed = $this->parseDataRow($row, $rowNumber, $race, $registeredBibs);

            if ($parsed === null) {
                continue; // Errors already logged
            }

            // Check for duplicate position
            if (in_array($parsed['position'], $usedPositions, true)) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'message' => "Position en double: {$parsed['position']}",
                ];

                continue;
            }
            $usedPositions[] = $parsed['position'];

            // Check for duplicate bib
            if (in_array($parsed['bib'], $usedBibs, true)) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'message' => "Dossard en double: {$parsed['bib']}",
                ];

                continue;
            }
            $usedBibs[] = $parsed['bib'];

            $this->parsedRows[] = $parsed;
        }
    }

    /**
     * Parse a single data row.
     */
    private function parseDataRow(array $row, int $rowNumber, Race $race, array $registeredBibs): ?array
    {
        // Extract values with fallbacks
        $position = $this->getColumnValue($row, 'position');
        $bib = $this->getColumnValue($row, 'bib');
        $pilote = $this->getColumnValue($row, 'pilote');
        $voiture = $this->getColumnValue($row, 'voiture');
        $categorie = $this->getColumnValue($row, 'catégorie');
        $temps = $this->getColumnValue($row, 'temps');

        // Validate position
        if (! is_numeric($position) || (int) $position < 1) {
            $this->errors[] = [
                'row' => $rowNumber,
                'message' => "Position invalide: {$position}",
            ];

            return null;
        }

        // Validate bib
        $bibInt = (int) $bib;
        if (! is_numeric($bib) || $bibInt < 1) {
            $this->errors[] = [
                'row' => $rowNumber,
                'message' => "Dossard invalide: {$bib}",
            ];

            return null;
        }

        // Check if bib is registered for this race
        if (! isset($registeredBibs[$bibInt])) {
            $this->errors[] = [
                'row' => $rowNumber,
                'message' => "Dossard non inscrit à cette course: {$bibInt}",
            ];

            return null;
        }

        // Parse time
        $timeMs = $this->parseTime($temps);
        if ($timeMs === null) {
            $this->errors[] = [
                'row' => $rowNumber,
                'message' => "Format de temps invalide: {$temps}",
            ];

            return null;
        }

        return [
            'position' => (int) $position,
            'bib' => $bibInt,
            'raw_time' => $temps,
            'time_ms' => $timeMs,
            'pilot_name' => $pilote ?: $registeredBibs[$bibInt]['pilot_name'],
            'car_description' => $voiture ?: $registeredBibs[$bibInt]['car_description'],
            'category_name' => $categorie ?: $registeredBibs[$bibInt]['category_name'],
            'race_registration_id' => $registeredBibs[$bibInt]['registration_id'],
        ];
    }

    /**
     * Get a column value from a row.
     */
    private function getColumnValue(array $row, string $column): ?string
    {
        if (! isset($this->columnMapping[$column])) {
            return null;
        }

        $index = $this->columnMapping[$column];

        return $row[$index] ?? null;
    }

    /**
     * Get all registered bibs for a race.
     *
     * @return array<int, array{registration_id: int, pilot_name: string, car_description: string, category_name: string|null}>
     */
    private function getRegisteredBibs(Race $race): array
    {
        $registrations = RaceRegistration::with(['pilot', 'car.category'])
            ->where('race_id', $race->id)
            ->whereIn('status', ['CONFIRMED', 'CHECKED_IN', 'TECH_OK'])
            ->get();

        $bibs = [];
        foreach ($registrations as $registration) {
            // race_number has a ValueObject accessor, so use getRawOriginal to get the int value
            $bib = (int) $registration->car->getRawOriginal('race_number');
            $bibs[$bib] = [
                'registration_id' => $registration->id,
                'pilot_name' => $registration->pilot->full_name,
                'car_description' => $registration->car->description ?? $registration->car->model,
                'category_name' => $registration->car->category?->name,
            ];
        }

        return $bibs;
    }

    /**
     * Parse a time string into milliseconds.
     *
     * Supported formats:
     * - MM:SS.mmm (2:34.567)
     * - MM:SS,mmm (2:34,567) - comma as decimal separator
     * - M:SS.mmm (2:34.567)
     * - SS.mmm (134.567)
     * - HH:MM:SS.mmm (1:02:34.567)
     */
    private function parseTime(string $time): ?int
    {
        $time = trim($time);
        $time = str_replace(',', '.', $time); // Normalize decimal separator

        // Pattern: HH:MM:SS.mmm (e.g., 1:02:34.567)
        if (preg_match('/^(\d+):(\d{1,2}):(\d{1,2})(?:\.(\d{1,3}))?$/', $time, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $seconds = (int) $matches[3];
            $milliseconds = isset($matches[4]) && $matches[4] !== '' ? (int) str_pad($matches[4], 3, '0') : 0;

            return ($hours * 3600 + $minutes * 60 + $seconds) * 1000 + $milliseconds;
        }

        // Pattern: MM:SS.mmm (e.g., 2:34.567 or 12:34.567)
        if (preg_match('/^(\d{1,2}):(\d{1,2})(?:\.(\d{1,3}))?$/', $time, $matches)) {
            $minutes = (int) $matches[1];
            $seconds = (int) $matches[2];
            $milliseconds = isset($matches[3]) && $matches[3] !== '' ? (int) str_pad($matches[3], 3, '0') : 0;

            return ($minutes * 60 + $seconds) * 1000 + $milliseconds;
        }

        // Pattern: SS.mmm (just seconds with milliseconds, e.g., 134.567)
        if (preg_match('/^(\d+)(?:\.(\d{1,3}))?$/', $time, $matches)) {
            $seconds = (int) $matches[1];
            $milliseconds = isset($matches[2]) && $matches[2] !== '' ? (int) str_pad($matches[2], 3, '0') : 0;

            return $seconds * 1000 + $milliseconds;
        }

        return null;
    }

    /**
     * Persist results to database.
     */
    private function persistResults(Race $race, ResultImport $import): bool
    {
        return DB::transaction(function () use ($race, $import) {
            // Delete existing results from this race (replace mode)
            RaceResult::where('race_id', $race->id)->delete();

            // Create new results
            foreach ($this->parsedRows as $row) {
                RaceResult::create([
                    'race_id' => $race->id,
                    'race_registration_id' => $row['race_registration_id'],
                    'result_import_id' => $import->id,
                    'position' => $row['position'],
                    'bib' => $row['bib'],
                    'raw_time' => $row['raw_time'],
                    'time_ms' => $row['time_ms'],
                    'pilot_name' => $row['pilot_name'],
                    'car_description' => $row['car_description'],
                    'category_name' => $row['category_name'],
                ]);
            }

            // Update import status
            $import->update([
                'status' => 'IMPORTED',
                'row_count' => count($this->parsedRows),
                'errors' => null,
            ]);

            // Update race status to RESULTS_READY
            $race->update(['status' => 'RESULTS_READY']);

            return true;
        });
    }

    /**
     * Mark import as failed.
     */
    private function fail(ResultImport $import): bool
    {
        $import->update([
            'status' => 'FAILED',
            'errors' => $this->errors,
        ]);

        return false;
    }

    /**
     * Get the errors from the last import attempt.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the parsed rows from the last import attempt.
     */
    public function getParsedRows(): array
    {
        return $this->parsedRows;
    }
}
