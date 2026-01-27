<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds indexes on frequently queried columns for performance optimization.
     */
    public function up(): void
    {
        // Races table indexes
        Schema::table('races', function (Blueprint $table) {
            $table->index('status', 'idx_races_status');
            $table->index(['season_id', 'status'], 'idx_races_season_status');
            $table->index('race_date', 'idx_races_date');
        });

        // Race registrations table indexes
        Schema::table('race_registrations', function (Blueprint $table) {
            $table->index('status', 'idx_registrations_status');
            $table->index(['race_id', 'status'], 'idx_registrations_race_status');
            $table->index(['pilot_id', 'status'], 'idx_registrations_pilot_status');
        });

        // Payments table indexes
        Schema::table('payments', function (Blueprint $table) {
            $table->index('status', 'idx_payments_status');
            $table->index(['race_registration_id', 'status'], 'idx_payments_registration_status');
        });

        // Passages table indexes (for timing queries) - only if table exists
        if (Schema::hasTable('passages')) {
            Schema::table('passages', function (Blueprint $table) {
                $table->index(['race_registration_id', 'checkpoint_id'], 'idx_passages_registration_checkpoint');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->dropIndex('idx_races_status');
            $table->dropIndex('idx_races_season_status');
            $table->dropIndex('idx_races_date');
        });

        Schema::table('race_registrations', function (Blueprint $table) {
            $table->dropIndex('idx_registrations_status');
            $table->dropIndex('idx_registrations_race_status');
            $table->dropIndex('idx_registrations_pilot_status');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_status');
            $table->dropIndex('idx_payments_registration_status');
        });

        if (Schema::hasTable('passages')) {
            Schema::table('passages', function (Blueprint $table) {
                $table->dropIndex('idx_passages_registration_checkpoint');
            });
        }
    }
};
