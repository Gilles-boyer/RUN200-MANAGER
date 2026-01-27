<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pilots', function (Blueprint $table) {
            $table->string('city')->nullable()->after('address');
            $table->string('postal_code', 10)->nullable()->after('city');
            $table->string('emergency_contact_name')->nullable()->after('guardian_phone');
            $table->string('emergency_contact_phone', 20)->nullable()->after('emergency_contact_name');
            $table->date('medical_certificate_date')->nullable()->after('emergency_contact_phone');
            $table->text('notes')->nullable()->after('medical_certificate_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilots', function (Blueprint $table) {
            $table->dropColumn([
                'city',
                'postal_code',
                'emergency_contact_name',
                'emergency_contact_phone',
                'medical_certificate_date',
                'notes',
            ]);
        });
    }
};
