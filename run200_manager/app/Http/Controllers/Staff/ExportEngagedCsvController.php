<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Models\Race;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller for exporting engaged pilots list as CSV.
 *
 * This export is designed to be imported into timing/chronometer systems.
 */
class ExportEngagedCsvController extends Controller
{
    /**
     * Export the list of engaged registrations for a race as CSV.
     *
     * Includes all pilots that have been accepted and beyond (VA/VT passed, etc.)
     * Columns: Bib, Nom, Prénom, Voiture, Catégorie
     */
    public function __invoke(Race $race): StreamedResponse
    {
        $registrations = $race->registrations()
            ->engaged()
            ->with(['pilot', 'car.category'])
            ->get()
            ->sortBy('car.race_number');

        $filename = sprintf(
            'engages_%s_%s.csv',
            str_replace(' ', '_', $race->slug ?? $race->name),
            now()->format('Y-m-d')
        );

        return response()->streamDownload(function () use ($registrations) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV Header
            fputcsv($handle, [
                'Bib',
                'Nom',
                'Prénom',
                'Voiture',
                'Catégorie',
            ], ';');

            // Data rows
            foreach ($registrations as $registration) {
                $pilot = $registration->pilot;
                $car = $registration->car;

                fputcsv($handle, [
                    $car?->race_number ?? '',
                    $pilot?->last_name ?? '',
                    $pilot?->first_name ?? '',
                    $car ? trim($car->make . ' ' . $car->model) : '',
                    $car?->category?->name ?? '',
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
