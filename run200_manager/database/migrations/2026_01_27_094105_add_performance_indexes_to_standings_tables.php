<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute des index composites pour optimiser les requêtes de classement.
     * Note: Les index sur race_registrations sont déjà créés dans
     * 2026_01_26_160229_add_status_indexes_for_performance.php
     */
    public function up(): void
    {
        // Index pour les standings généraux (utilisé pour le classement)
        if (Schema::hasTable('season_standings')) {
            Schema::table('season_standings', function (Blueprint $table) {
                if (! $this->indexExists('season_standings', 'idx_standings_ranking')) {
                    $table->index(['season_id', 'total_points', 'races_count'], 'idx_standings_ranking');
                }
                if (! $this->indexExists('season_standings', 'idx_standings_season_rank')) {
                    $table->index(['season_id', 'rank'], 'idx_standings_season_rank');
                }
            });
        }

        // Index pour les standings par catégorie
        if (Schema::hasTable('season_category_standings')) {
            Schema::table('season_category_standings', function (Blueprint $table) {
                if (! $this->indexExists('season_category_standings', 'idx_cat_standings_ranking')) {
                    $table->index(['season_id', 'car_category_id', 'total_points'], 'idx_cat_standings_ranking');
                }
                if (! $this->indexExists('season_category_standings', 'idx_cat_standings_rank')) {
                    $table->index(['season_id', 'car_category_id', 'rank'], 'idx_cat_standings_rank');
                }
            });
        }

        // Index pour les résultats de course (utilisé lors du rebuild)
        if (Schema::hasTable('race_results')) {
            Schema::table('race_results', function (Blueprint $table) {
                if (! $this->indexExists('race_results', 'idx_results_race_position')) {
                    $table->index(['race_id', 'position'], 'idx_results_race_position');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('season_standings')) {
            Schema::table('season_standings', function (Blueprint $table) {
                if ($this->indexExists('season_standings', 'idx_standings_ranking')) {
                    $table->dropIndex('idx_standings_ranking');
                }
                if ($this->indexExists('season_standings', 'idx_standings_season_rank')) {
                    $table->dropIndex('idx_standings_season_rank');
                }
            });
        }

        if (Schema::hasTable('season_category_standings')) {
            Schema::table('season_category_standings', function (Blueprint $table) {
                if ($this->indexExists('season_category_standings', 'idx_cat_standings_ranking')) {
                    $table->dropIndex('idx_cat_standings_ranking');
                }
                if ($this->indexExists('season_category_standings', 'idx_cat_standings_rank')) {
                    $table->dropIndex('idx_cat_standings_rank');
                }
            });
        }

        if (Schema::hasTable('race_results')) {
            Schema::table('race_results', function (Blueprint $table) {
                if ($this->indexExists('race_results', 'idx_results_race_position')) {
                    $table->dropIndex('idx_results_race_position');
                }
            });
        }
    }

    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = $connection->select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains('name', $indexName);
        }

        if ($driver === 'mysql') {
            $indexes = $connection->select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

            return count($indexes) > 0;
        }

        if ($driver === 'pgsql') {
            $indexes = $connection->select('SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?', [$table, $indexName]);

            return count($indexes) > 0;
        }

        return false;
    }
};
