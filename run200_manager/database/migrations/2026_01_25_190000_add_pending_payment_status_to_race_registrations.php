<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL, modify the enum
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE race_registrations MODIFY COLUMN status ENUM('PENDING_PAYMENT', 'PENDING_VALIDATION', 'ACCEPTED', 'REFUSED', 'CANCELLED') DEFAULT 'PENDING_PAYMENT'");
        }
        // SQLite doesn't support ENUM, it uses TEXT and the model handles validation
        // No migration needed for SQLite as it accepts any string value
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE race_registrations MODIFY COLUMN status ENUM('PENDING_VALIDATION', 'ACCEPTED', 'REFUSED') DEFAULT 'PENDING_VALIDATION'");
        }
    }
};
