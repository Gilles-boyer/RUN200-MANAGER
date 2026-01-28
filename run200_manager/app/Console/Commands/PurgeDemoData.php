<?php

namespace App\Console\Commands;

use App\Models\Car;
use App\Models\CarTechInspectionHistory;
use App\Models\CheckpointPassage;
use App\Models\EngagementForm;
use App\Models\PaddockSpot;
use App\Models\Payment;
use App\Models\Pilot;
use App\Models\QrToken;
use App\Models\Race;
use App\Models\RaceDocument;
use App\Models\RaceDocumentVersion;
use App\Models\RaceNotification;
use App\Models\RaceRegistration;
use App\Models\RaceResult;
use App\Models\ResultImport;
use App\Models\Season;
use App\Models\SeasonCategoryStanding;
use App\Models\SeasonPointsRule;
use App\Models\SeasonStanding;
use App\Models\TechInspection;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Commande pour purger les données de test/démo de la base de données.
 *
 * Cette commande supprime toutes les données transactionnelles tout en
 * conservant les données de référence (rôles, permissions, catégories).
 *
 * Usage:
 *   php artisan app:purge-demo-data           # Mode interactif
 *   php artisan app:purge-demo-data --force   # Sans confirmation
 *   php artisan app:purge-demo-data --keep-admin  # Garder le compte admin
 */
class PurgeDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:purge-demo-data
                            {--force : Exécuter sans confirmation}
                            {--keep-admin : Conserver le compte administrateur}
                            {--keep-seasons : Conserver les saisons}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purger toutes les données de test/démo (pilotes, courses, inscriptions, etc.)';

    /**
     * Tables à purger (dans l'ordre pour respecter les FK).
     */
    protected array $tablesToPurge = [
        // Données dépendantes d'abord
        'checkpoint_passages',
        'qr_tokens',
        'tech_inspections',
        'car_tech_inspection_histories',
        'engagement_forms',
        'payments',
        'race_results',
        'result_imports',
        'race_registrations',
        'race_document_versions',
        'race_documents',
        'race_notifications',
        'paddock_spots',
        'season_category_standings',
        'season_standings',
        'season_points_rules',
        'races',
        'cars',
        'pilots',
        'activity_log',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->warn('⚠️  ATTENTION : Cette commande va SUPPRIMER des données !');
        $this->newLine();

        // Afficher ce qui va être supprimé
        $this->info('Les données suivantes seront supprimées :');
        $this->table(
            ['Table', 'Nombre d\'enregistrements'],
            $this->getTableCounts()
        );

        // Demander confirmation
        if (! $this->option('force')) {
            if (! $this->confirm('Êtes-vous sûr de vouloir purger ces données ?', false)) {
                $this->info('Opération annulée.');

                return Command::SUCCESS;
            }

            // Double confirmation en production
            if (app()->environment('production')) {
                $this->error('🚨 VOUS ÊTES EN PRODUCTION !');
                if (! $this->confirm('Confirmez-vous VRAIMENT la suppression en PRODUCTION ?', false)) {
                    $this->info('Opération annulée.');

                    return Command::SUCCESS;
                }
            }
        }

        $this->newLine();
        $this->info('🗑️  Purge des données en cours...');

        try {
            DB::beginTransaction();

            // Désactiver temporairement les FK
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
            } elseif (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            }

            $deleted = $this->purgeData();

            // Optionnel : vider les saisons (AVANT de réactiver les FK)
            $seasonsDeleted = 0;
            if (! $this->option('keep-seasons')) {
                $seasonsDeleted = Season::count();
                if ($seasonsDeleted > 0) {
                    DB::table('seasons')->delete();
                    $deleted[] = ['seasons', $seasonsDeleted];
                    $this->line("   ✓ seasons : {$seasonsDeleted} enregistrement(s)");
                }
            }

            // Supprimer les utilisateurs (AVANT de réactiver les FK)
            $usersDeleted = $this->purgeUsers();
            if ($usersDeleted > 0) {
                $deleted[] = ['users', $usersDeleted];
            }

            // Réactiver les FK
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            } elseif (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            }

            DB::commit();

            $this->newLine();
            $this->info('✅ Purge terminée avec succès !');
            $this->table(
                ['Table', 'Enregistrements supprimés'],
                $deleted
            );

            $this->newLine();
            $this->info('💡 Pour réinitialiser avec les données de référence :');
            $this->line('   php artisan db:seed --class=ProductionSeeder');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();

            // Réactiver les FK en cas d'erreur
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            }

            $this->error('❌ Erreur lors de la purge : '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Récupérer le nombre d'enregistrements par table.
     */
    private function getTableCounts(): array
    {
        $counts = [];

        foreach ($this->tablesToPurge as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                if ($count > 0) {
                    $counts[] = [$table, $count];
                }
            }
        }

        // Ajouter les utilisateurs
        $userCount = User::whereDoesntHave('roles', function ($q) {
            $q->where('name', 'ADMIN');
        })->count();

        if ($this->option('keep-admin')) {
            $counts[] = ['users (hors admin)', $userCount];
        } else {
            $counts[] = ['users', User::count()];
        }

        // Saisons
        if (! $this->option('keep-seasons')) {
            $counts[] = ['seasons', Season::count()];
        }

        return $counts;
    }

    /**
     * Purger les données.
     */
    private function purgeData(): array
    {
        $deleted = [];

        foreach ($this->tablesToPurge as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                if ($count > 0) {
                    DB::table($table)->delete();
                    $deleted[] = [$table, $count];
                    $this->line("   ✓ {$table} : {$count} enregistrement(s)");
                }
            }
        }

        return $deleted;
    }

    /**
     * Purger les utilisateurs.
     *
     * @return int Nombre d'utilisateurs supprimés
     */
    private function purgeUsers(): int
    {
        if ($this->option('keep-admin')) {
            // Supprimer tous les utilisateurs SAUF les admins
            $adminIds = User::role('ADMIN')->pluck('id');
            $count = User::whereNotIn('id', $adminIds)->count();

            if ($count > 0) {
                // Supprimer les relations model_has_roles d'abord
                DB::table('model_has_roles')
                    ->where('model_type', User::class)
                    ->whereNotIn('model_id', $adminIds)
                    ->delete();

                User::whereNotIn('id', $adminIds)->forceDelete();
                $this->line("   ✓ users (hors admin) : {$count} enregistrement(s)");
            }

            return $count;
        } else {
            $count = User::count();
            if ($count > 0) {
                // Supprimer toutes les relations
                DB::table('model_has_roles')->where('model_type', User::class)->delete();
                User::query()->forceDelete();
                $this->line("   ✓ users : {$count} enregistrement(s)");
            }

            return $count;
        }
    }
}
