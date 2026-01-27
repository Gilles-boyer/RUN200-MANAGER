<?php

namespace App\Console\Commands;

use App\Models\CheckpointPassage;
use App\Models\EngagementForm;
use App\Models\RaceRegistration;
use Illuminate\Console\Command;

class MigrateEngagementValidations extends Command
{
    protected $signature = 'engagement:migrate-validations';

    protected $description = 'Migrate existing tech/admin validations to engagement forms';

    public function handle()
    {
        $this->info('Recherche de fiches d\'engagement à mettre à jour...');

        // Forcer la mise à jour de TOUTES les fiches
        $engagements = EngagementForm::all();

        $techUpdated = 0;
        $adminUpdated = 0;

        foreach ($engagements as $engagement) {
            $registration = RaceRegistration::with(['techInspection.inspector'])
                ->find($engagement->race_registration_id);

            if (! $registration) {
                continue;
            }

            // Mise à jour tech inspection - FORCER LA MISE À JOUR
            if ($registration->techInspection) {
                $techInspection = $registration->techInspection;
                $this->info("Tech inspection found for registration {$registration->id}");
                $this->info('  Inspector: '.($techInspection->inspector->name ?? 'N/A'));
                $this->info('  Inspected at: '.($techInspection->inspected_at ?? 'NULL'));
                $this->info('  Status: '.$techInspection->status);

                $engagement->tech_controller_name = $techInspection->inspector->name ?? 'Contrôleur Technique';
                $engagement->tech_checked_at = $techInspection->inspected_at; // Utiliser inspected_at, pas checked_at
                $techUpdated++;
            }

            $adminCheck = CheckpointPassage::where('race_registration_id', $registration->id)
                ->whereHas('checkpoint', function ($q) {
                    $q->where('code', 'ADMIN_CHECK');
                })
                ->with('scanner')
                ->first();

            if ($adminCheck) {
                $this->info("Admin check found for registration {$registration->id}");
                $this->info('  Scanner: '.($adminCheck->scanner->name ?? 'N/A'));
                $this->info('  Scanned at: '.($adminCheck->scanned_at ?? 'NULL'));

                $engagement->admin_validated_by = $adminCheck->scanned_by;
                $engagement->admin_validated_at = $adminCheck->scanned_at;
                $adminUpdated++;
            }

            $engagement->save();
        }

        $this->info("\n=== Résultats ===");
        $this->info("Contrôles techniques migrés: {$techUpdated}");
        $this->info("Contrôles administratifs migrés: {$adminUpdated}");
        $this->info('Opération terminée avec succès!');

        return 0;
    }
}
