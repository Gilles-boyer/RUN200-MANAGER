<?php

namespace App\Console\Commands;

use App\Models\CarTechInspectionHistory;
use App\Models\RaceRegistration;
use App\Models\TechInspection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncTechInspectionHistory extends Command
{
    protected $signature = 'sync:tech-inspection-history {--dry-run : Show what would be synced without making changes}';

    protected $description = 'Synchronize CarTechInspectionHistory with TechInspection records';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Mode DRY-RUN: aucune modification ne sera effectuée.');
        }

        $this->info('Analyse des enregistrements...');

        // 1. Find TechInspections without corresponding CarTechInspectionHistory
        $techInspections = TechInspection::with(['registration.car', 'inspector'])->get();
        $this->info("TechInspections trouvés: {$techInspections->count()}");

        $created = 0;
        $updated = 0;
        $orphansDeleted = 0;

        DB::beginTransaction();

        try {
            foreach ($techInspections as $inspection) {
                $history = CarTechInspectionHistory::where('tech_inspection_id', $inspection->id)->first();

                if (! $history) {
                    // Create missing history
                    $this->warn("  [MISSING] TechInspection #{$inspection->id} - Reg #{$inspection->race_registration_id}");

                    if (! $dryRun && $inspection->registration) {
                        CarTechInspectionHistory::create([
                            'car_id' => $inspection->registration->car_id,
                            'race_registration_id' => $inspection->race_registration_id,
                            'tech_inspection_id' => $inspection->id,
                            'status' => $inspection->status,
                            'notes' => $inspection->notes,
                            'inspected_by' => $inspection->inspected_by,
                            'inspected_at' => $inspection->inspected_at,
                        ]);
                        $this->info("    -> Historique créé");
                    }
                    $created++;
                } else {
                    // Check if notes are synced
                    $needsUpdate = false;
                    $changes = [];

                    if ($history->notes !== $inspection->notes) {
                        $changes[] = "notes: '{$history->notes}' -> '{$inspection->notes}'";
                        $needsUpdate = true;
                    }

                    if ($history->status !== $inspection->status) {
                        $changes[] = "status: '{$history->status}' -> '{$inspection->status}'";
                        $needsUpdate = true;
                    }

                    if ($needsUpdate) {
                        $this->warn("  [MISMATCH] History #{$history->id} for TechInspection #{$inspection->id}");
                        foreach ($changes as $change) {
                            $this->line("    -> $change");
                        }

                        if (! $dryRun) {
                            $history->update([
                                'notes' => $inspection->notes,
                                'status' => $inspection->status,
                            ]);
                            $this->info("    -> Synchronisé");
                        }
                        $updated++;
                    }
                }
            }

            // 2. Find orphan CarTechInspectionHistory (where tech_inspection_id points to deleted TechInspection)
            $orphans = CarTechInspectionHistory::whereNotNull('tech_inspection_id')
                ->whereDoesntHave('techInspection')
                ->get();

            if ($orphans->count() > 0) {
                $this->warn("\nHistoriques orphelins trouvés: {$orphans->count()}");
                foreach ($orphans as $orphan) {
                    $this->line("  [ORPHAN] History #{$orphan->id} - Car #{$orphan->car_id} (tech_inspection_id: {$orphan->tech_inspection_id})");
                    if (! $dryRun) {
                        $orphan->delete();
                        $this->info("    -> Supprimé");
                    }
                    $orphansDeleted++;
                }
            }

            // 3. Sync EngagementForm tech_notes with TechInspection notes
            $this->info("\nSynchronisation des notes EngagementForm...");
            $registrationsWithTech = RaceRegistration::with(['techInspection', 'engagementForm'])
                ->whereHas('techInspection')
                ->whereHas('engagementForm')
                ->get();

            $engagementUpdated = 0;
            foreach ($registrationsWithTech as $reg) {
                if ($reg->engagementForm && $reg->techInspection) {
                    if ($reg->engagementForm->tech_notes !== $reg->techInspection->notes) {
                        $this->warn("  [MISMATCH] EngagementForm #{$reg->engagementForm->id}");
                        $this->line("    -> tech_notes: '{$reg->engagementForm->tech_notes}' -> '{$reg->techInspection->notes}'");

                        if (! $dryRun) {
                            $reg->engagementForm->update([
                                'tech_notes' => $reg->techInspection->notes,
                            ]);
                            $this->info("    -> Synchronisé");
                        }
                        $engagementUpdated++;
                    }
                }
            }

            if ($dryRun) {
                DB::rollBack();
                $this->info("\n[DRY-RUN] Aucune modification effectuée.");
            } else {
                DB::commit();
            }

            $this->newLine();
            $this->info("=== Résumé ===");
            $this->info("Historiques créés: $created");
            $this->info("Historiques mis à jour: $updated");
            $this->info("Historiques orphelins supprimés: $orphansDeleted");
            $this->info("EngagementForm synchronisés: $engagementUpdated");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Erreur: " . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
