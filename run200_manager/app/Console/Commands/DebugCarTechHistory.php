<?php

namespace App\Console\Commands;

use App\Models\Car;
use App\Models\CarTechInspectionHistory;
use App\Models\RaceRegistration;
use App\Models\TechInspection;
use Illuminate\Console\Command;

class DebugCarTechHistory extends Command
{
    protected $signature = 'debug:car-tech {carId}';

    protected $description = 'Debug tech inspection history for a specific car';

    public function handle(): int
    {
        $carId = $this->argument('carId');

        $car = Car::find($carId);
        if (! $car) {
            $this->error("Car #{$carId} not found");
            return Command::FAILURE;
        }

        $this->info("=== Car #{$carId} ===");
        $this->info("Brand: {$car->brand}, Model: {$car->model}");

        // Check CarTechInspectionHistory
        $this->newLine();
        $this->info("=== CarTechInspectionHistory ===");
        $histories = CarTechInspectionHistory::where('car_id', $carId)->get();
        $this->info("Count: {$histories->count()}");

        foreach ($histories as $h) {
            $this->line("  History #{$h->id}:");
            $this->line("    - tech_inspection_id: " . ($h->tech_inspection_id ?? 'NULL'));
            $this->line("    - race_registration_id: " . ($h->race_registration_id ?? 'NULL'));
            $this->line("    - status: {$h->status}");
            $this->line("    - notes: " . ($h->notes ?? 'NULL'));
            $this->line("    - inspected_at: " . ($h->inspected_at ?? 'NULL'));
        }

        // Check registrations for this car
        $this->newLine();
        $this->info("=== RaceRegistrations for this car ===");
        $registrations = RaceRegistration::where('car_id', $carId)
            ->with(['techInspection', 'race', 'passages.checkpoint'])
            ->get();
        $this->info("Count: {$registrations->count()}");

        foreach ($registrations as $reg) {
            $this->line("  Registration #{$reg->id} (Race: {$reg->race->name}):");
            $this->line("    - status: {$reg->status}");

            if ($reg->techInspection) {
                $this->line("    - TechInspection #{$reg->techInspection->id}:");
                $this->line("      - status: {$reg->techInspection->status}");
                $this->line("      - notes: " . ($reg->techInspection->notes ?? 'NULL'));

                // Check if history exists for this tech inspection
                $historyExists = CarTechInspectionHistory::where('tech_inspection_id', $reg->techInspection->id)->exists();
                $this->line("      - Has CarTechInspectionHistory: " . ($historyExists ? 'YES' : 'NO'));
            } else {
                $this->line("    - TechInspection: NONE");
            }

            // Check TECH_CHECK passage
            $techPassage = $reg->passages->first(function ($p) {
                return $p->checkpoint && $p->checkpoint->code === 'TECH_CHECK';
            });
            if ($techPassage) {
                $this->line("    - TECH_CHECK passage: YES (at {$techPassage->scanned_at})");
                $this->line("      - staff_note: " . ($techPassage->meta['staff_note'] ?? 'NULL'));
            } else {
                $this->line("    - TECH_CHECK passage: NO");
            }
        }

        return Command::SUCCESS;
    }
}
