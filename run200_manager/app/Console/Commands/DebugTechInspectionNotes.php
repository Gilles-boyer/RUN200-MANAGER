<?php

namespace App\Console\Commands;

use App\Models\CarTechInspectionHistory;
use App\Models\RaceRegistration;
use App\Models\TechInspection;
use Illuminate\Console\Command;

class DebugTechInspectionNotes extends Command
{
    protected $signature = 'debug:tech-notes {car_id?}';

    protected $description = 'Debug les notes de contrôle technique pour une voiture';

    public function handle()
    {
        $carId = $this->argument('car_id') ?? 76;

        $this->info("Vérification des données pour la voiture ID: {$carId}");
        $this->newLine();

        // 1. CarTechInspectionHistory
        $history = CarTechInspectionHistory::where('car_id', $carId)->first();
        if (! $history) {
            $this->error("Aucun historique trouvé pour la voiture {$carId}");

            return 1;
        }

        $this->info('=== CarTechInspectionHistory ===');
        $this->line("ID: {$history->id}");
        $this->line('Notes: '.($history->notes ?? 'NULL'));
        $this->line('tech_inspection_id: '.($history->tech_inspection_id ?? 'NULL'));
        $this->line('race_registration_id: '.($history->race_registration_id ?? 'NULL'));
        $this->newLine();

        // 2. TechInspection via tech_inspection_id
        if ($history->tech_inspection_id) {
            $ti = TechInspection::find($history->tech_inspection_id);
            if ($ti) {
                $this->info('=== TechInspection (via tech_inspection_id) ===');
                $this->line("ID: {$ti->id}");
                $this->line('Notes: '.($ti->notes ?? 'NULL'));
            }
        }

        // 3. TechInspection via race_registration_id
        if ($history->race_registration_id) {
            $ti = TechInspection::where('race_registration_id', $history->race_registration_id)->first();
            if ($ti) {
                $this->info('=== TechInspection (via race_registration_id) ===');
                $this->line("ID: {$ti->id}");
                $this->line('Notes: '.($ti->notes ?? 'NULL'));
            } else {
                $this->warn('Aucun TechInspection trouvé via race_registration_id');
            }
        }

        // 4. EngagementForm
        if ($history->race_registration_id) {
            $reg = RaceRegistration::with('engagementForm')->find($history->race_registration_id);
            if ($reg && $reg->engagementForm) {
                $this->newLine();
                $this->info('=== EngagementForm ===');
                $this->line('tech_notes: '.($reg->engagementForm->tech_notes ?? 'NULL'));
                $this->line('tech_checked_at: '.($reg->engagementForm->tech_checked_at ?? 'NULL'));
                $this->line('tech_controller_name: '.($reg->engagementForm->tech_controller_name ?? 'NULL'));
            } else {
                $this->warn('Aucun EngagementForm trouvé');
            }
        }

        // 5. CheckpointPassage TECH_CHECK
        if ($history->race_registration_id) {
            $techCheckpoint = \App\Models\Checkpoint::where('code', 'TECH_CHECK')->first();
            if ($techCheckpoint) {
                $passage = \App\Models\CheckpointPassage::where('race_registration_id', $history->race_registration_id)
                    ->where('checkpoint_id', $techCheckpoint->id)
                    ->first();
                if ($passage) {
                    $this->newLine();
                    $this->info('=== CheckpointPassage TECH_CHECK ===');
                    $this->line('ID: '.$passage->id);
                    $this->line('meta: '.json_encode($passage->meta));
                    $staffNote = $passage->meta['staff_note'] ?? null;
                    $this->line('staff_note: '.($staffNote ?? 'NULL'));
                } else {
                    $this->warn('Aucun passage TECH_CHECK trouvé');
                }
            }
        }

        return 0;
    }
}
