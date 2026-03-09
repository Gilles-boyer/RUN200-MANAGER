<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add missing statuses: COMPLETED, CANCELLED, ARCHIVED
     */
    public function up(): void
    {
        // Modify the ENUM to include all statuses
        DB::statement("ALTER TABLE races MODIFY COLUMN status ENUM('DRAFT', 'OPEN', 'CLOSED', 'RUNNING', 'RESULTS_READY', 'PUBLISHED', 'COMPLETED', 'CANCELLED', 'ARCHIVED') DEFAULT 'DRAFT'");
    }

    /**
     * Reverse the migrations.
     * Revert to original ENUM values
     */
    public function down(): void
    {
        // First, convert any new statuses back to valid ones
        DB::statement("UPDATE races SET status = 'PUBLISHED' WHERE status IN ('COMPLETED', 'ARCHIVED')");
        DB::statement("UPDATE races SET status = 'CLOSED' WHERE status = 'CANCELLED'");

        // Then modify the ENUM back
        DB::statement("ALTER TABLE races MODIFY COLUMN status ENUM('DRAFT', 'OPEN', 'CLOSED', 'RUNNING', 'RESULTS_READY', 'PUBLISHED') DEFAULT 'DRAFT'");
    }
};
