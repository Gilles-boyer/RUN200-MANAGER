<?php

namespace App\Console\Commands;

use App\Models\CarTechInspectionHistory;
use App\Models\TechInspection;
use Illuminate\Console\Command;

class SyncTechInspectionNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tech-inspections:sync-notes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les notes manquantes dans l\'historique depuis les TechInspections';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Synchronisation des notes de contrôle technique...');

        // Récupérer les entrées d'historique sans notes
        $histories = CarTechInspectionHistory::whereNull('notes')
            ->orWhere('notes', '')
            ->get();

        $this->info("Entrées d'historique sans notes : {$histories->count()}");

        $updated = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar($histories->count());
        $bar->start();

        foreach ($histories as $history) {
            $notes = null;

            // Essayer via tech_inspection_id
            if ($history->tech_inspection_id) {
                $techInspection = TechInspection::find($history->tech_inspection_id);
                if ($techInspection && $techInspection->notes) {
                    $notes = $techInspection->notes;
                }
            }

            // Sinon essayer via race_registration_id
            if (! $notes && $history->race_registration_id) {
                $techInspection = TechInspection::where('race_registration_id', $history->race_registration_id)->first();
                if ($techInspection && $techInspection->notes) {
                    $notes = $techInspection->notes;
                    // Mettre aussi à jour le tech_inspection_id si manquant
                    if (! $history->tech_inspection_id) {
                        $history->tech_inspection_id = $techInspection->id;
                    }
                }
            }

            // Sinon essayer via engagement form
            if (! $notes && $history->race_registration_id) {
                $history->load('registration.engagementForm');
                if ($history->registration?->engagementForm?->tech_notes) {
                    $notes = $history->registration->engagementForm->tech_notes;
                }
            }

            if ($notes) {
                $history->notes = $notes;
                $history->save();
                $updated++;
            } else {
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine(2);
        $this->info('Synchronisation terminée !');
        $this->info("Notes mises à jour : {$updated}");
        $this->info("Entrées sans notes disponibles : {$skipped}");

        return 0;
    }
}
