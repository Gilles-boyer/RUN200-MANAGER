<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds checkpoint-related statuses to the race_registrations status ENUM:
     * - ADMIN_CHECKED: Validation administrative effectuée
     * - TECH_CHECKED_OK: Contrôle technique OK
     * - TECH_CHECKED_FAIL: Contrôle technique échoué
     * - ENTRY_SCANNED: Entrée effectuée
     * - BRACELET_GIVEN: Bracelet remis
     * - RESULTS_IMPORTED: Résultats importés
     * - PUBLISHED: Résultats publiés
     * - SUBMITTED: Inscription soumise
     */
    public function up(): void
    {
        // Only run for MySQL - SQLite doesn't need ENUM modifications
        // SQLite stores ENUMs as TEXT and allows any value
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE race_registrations MODIFY COLUMN status ENUM(
                'SUBMITTED',
                'PENDING_PAYMENT',
                'PENDING_VALIDATION',
                'ACCEPTED',
                'REFUSED',
                'CANCELLED',
                'ADMIN_CHECKED',
                'TECH_CHECKED_OK',
                'TECH_CHECKED_FAIL',
                'ENTRY_SCANNED',
                'BRACELET_GIVEN',
                'RESULTS_IMPORTED',
                'PUBLISHED'
            ) DEFAULT 'PENDING_PAYMENT'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            // First, reset any new status values back to ACCEPTED to avoid data loss
            DB::statement("UPDATE race_registrations SET status = 'ACCEPTED' WHERE status IN (
                'ADMIN_CHECKED',
                'TECH_CHECKED_OK',
                'TECH_CHECKED_FAIL',
                'ENTRY_SCANNED',
                'BRACELET_GIVEN',
                'RESULTS_IMPORTED',
                'PUBLISHED',
                'SUBMITTED'
            )");

            DB::statement("ALTER TABLE race_registrations MODIFY COLUMN status ENUM(
                'PENDING_PAYMENT',
                'PENDING_VALIDATION',
                'ACCEPTED',
                'REFUSED',
                'CANCELLED'
            ) DEFAULT 'PENDING_PAYMENT'");
        }
    }
};
