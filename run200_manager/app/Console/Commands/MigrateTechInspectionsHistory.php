<?php

namespace App\Console\Commands;

use App\Models\CarTechInspectionHistory;
use App\Models\TechInspection;
use Illuminate\Console\Command;

class MigrateTechInspectionsHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tech-inspections:migrate-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrer les contrôles techniques existants vers la table d\'historique';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Début de la migration des contrôles techniques...');

        // Récupérer tous les contrôles techniques avec leurs relations
        $techInspections = TechInspection::with('registration.car')->get();

        $this->info("Nombre de contrôles techniques à migrer : {$techInspections->count()}");

        $bar = $this->output->createProgressBar($techInspections->count());
        $bar->start();

        $migrated = 0;
        $skipped = 0;

        foreach ($techInspections as $techInspection) {
            // Vérifier que l'inscription a une voiture
            if (! $techInspection->registration || ! $techInspection->registration->car) {
                $this->newLine();
                $this->warn("Contrôle technique #{$techInspection->id} sans voiture associée - ignoré");
                $skipped++;
                $bar->advance();

                continue;
            }

            $car = $techInspection->registration->car;

            // Vérifier si l'entrée existe déjà dans l'historique
            $exists = CarTechInspectionHistory::where('tech_inspection_id', $techInspection->id)->exists();

            if ($exists) {
                $skipped++;
                $bar->advance();

                continue;
            }

            // Créer l'entrée dans l'historique
            CarTechInspectionHistory::create([
                'car_id' => $car->id,
                'race_registration_id' => $techInspection->race_registration_id,
                'tech_inspection_id' => $techInspection->id,
                'status' => $techInspection->status,
                'notes' => $techInspection->notes,
                'inspection_details' => null, // Pas de détails JSON dans l'ancienne table
                'inspected_by' => $techInspection->inspected_by,
                'inspected_at' => $techInspection->inspected_at,
            ]);

            $migrated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Migration terminée !');
        $this->info("Contrôles migrés : {$migrated}");
        $this->info("Contrôles ignorés : {$skipped}");

        return Command::SUCCESS;
    }
}
