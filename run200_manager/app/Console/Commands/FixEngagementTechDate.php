<?php

namespace App\Console\Commands;

use App\Models\EngagementForm;
use App\Models\RaceRegistration;
use Illuminate\Console\Command;

class FixEngagementTechDate extends Command
{
    protected $signature = 'fix:engagement-tech';

    protected $description = 'Fix tech inspection date in engagement forms';

    public function handle()
    {
        $this->info('Correction des dates de contrôle technique...');

        $engagements = EngagementForm::all();
        $fixed = 0;

        foreach ($engagements as $engagement) {
            $registration = RaceRegistration::with(['techInspection.inspector'])
                ->find($engagement->race_registration_id);

            if (! $registration || ! $registration->techInspection) {
                continue;
            }

            $techInspection = $registration->techInspection;

            $this->info("\nFiche #{$engagement->id} - Inscription #{$registration->id}");
            $this->info('  Inspector: '.($techInspection->inspector->name ?? 'N/A'));
            $this->info('  Inspected at: '.($techInspection->inspected_at ?? 'NULL'));

            // Mise à jour
            $engagement->tech_controller_name = $techInspection->inspector->name ?? 'Contrôleur Technique';
            $engagement->tech_checked_at = $techInspection->inspected_at;
            $engagement->tech_notes = $techInspection->notes;
            $engagement->save();

            $this->info('  ✓ Mise à jour effectuée');
            $fixed++;
        }

        $this->info("\n=== Terminé ===");
        $this->info("$fixed fiches mises à jour");

        return 0;
    }
}
