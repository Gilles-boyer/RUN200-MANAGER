<?php

namespace App\Console\Commands;

use App\Models\CarTechInspectionHistory;
use App\Models\Checkpoint;
use App\Models\CheckpointPassage;
use App\Models\TechInspection;
use Illuminate\Console\Command;

class SyncTechInspectionsFromPassages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tech-inspections:sync-from-passages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les contrôles techniques depuis les passages de checkpoint TECH_CHECK';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Synchronisation des contrôles techniques depuis les passages...');

        // Trouver le checkpoint TECH_CHECK
        $techCheckpoint = Checkpoint::where('code', 'TECH_CHECK')->first();

        if (! $techCheckpoint) {
            $this->error('Checkpoint TECH_CHECK non trouvé');
            return 1;
        }

        // Récupérer tous les passages de TECH_CHECK
        $passages = CheckpointPassage::with(['registration.car', 'scanner'])
            ->where('checkpoint_id', $techCheckpoint->id)
            ->get();

        $this->info("Nombre de passages TECH_CHECK trouvés : {$passages->count()}");

        $created = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar($passages->count());
        $bar->start();

        foreach ($passages as $passage) {
            // Vérifier que l'inscription existe
            if (! $passage->registration) {
                $this->newLine();
                $this->warn("Passage #{$passage->id} sans inscription - ignoré");
                $skipped++;
                $bar->advance();
                continue;
            }

            // Vérifier si une TechInspection existe déjà
            $existingInspection = TechInspection::where('race_registration_id', $passage->race_registration_id)->first();

            if ($existingInspection) {
                // TechInspection existe, vérifier si l'historique existe
                $historyExists = CarTechInspectionHistory::where('tech_inspection_id', $existingInspection->id)->exists();

                if (! $historyExists && $passage->registration->car) {
                    // Créer l'entrée historique manquante
                    CarTechInspectionHistory::create([
                        'car_id' => $passage->registration->car_id,
                        'race_registration_id' => $passage->race_registration_id,
                        'tech_inspection_id' => $existingInspection->id,
                        'status' => $existingInspection->status,
                        'notes' => $existingInspection->notes,
                        'inspected_by' => $existingInspection->inspected_by,
                        'inspected_at' => $existingInspection->inspected_at,
                    ]);
                    $created++;
                } else {
                    $skipped++;
                }

                $bar->advance();
                continue;
            }

            // Pas de TechInspection, créer depuis le passage
            if (! $passage->registration->car) {
                $this->newLine();
                $this->warn("Inscription #{$passage->race_registration_id} sans voiture - ignoré");
                $skipped++;
                $bar->advance();
                continue;
            }

            // Déterminer le statut basé sur le statut de l'inscription
            $status = 'OK';
            if ($passage->registration->status === 'TECH_CHECKED_FAIL') {
                $status = 'FAIL';
            }

            // Créer la TechInspection
            $techInspection = TechInspection::create([
                'race_registration_id' => $passage->race_registration_id,
                'status' => $status,
                'notes' => null,
                'inspected_by' => $passage->scanned_by,
                'inspected_at' => $passage->scanned_at,
            ]);

            // Créer l'historique
            CarTechInspectionHistory::create([
                'car_id' => $passage->registration->car_id,
                'race_registration_id' => $passage->race_registration_id,
                'tech_inspection_id' => $techInspection->id,
                'status' => $status,
                'notes' => null,
                'inspected_by' => $passage->scanned_by,
                'inspected_at' => $passage->scanned_at,
            ]);

            $created++;
            $bar->advance();
        }

        $bar->finish();

        $this->newLine(2);
        $this->info('Synchronisation terminée !');
        $this->info("Entrées créées : {$created}");
        $this->info("Entrées ignorées : {$skipped}");

        return 0;
    }
}
