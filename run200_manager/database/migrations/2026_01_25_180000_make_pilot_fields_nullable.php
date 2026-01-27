<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Make some pilot fields nullable to allow partial profile creation
     * during registration, with completion required later.
     */
    public function up(): void
    {
        Schema::table('pilots', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->change();
            $table->string('birth_place')->nullable()->change();
            $table->string('address', 500)->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('postal_code', 10)->nullable()->change();
            $table->string('emergency_contact_name')->nullable()->change();
            $table->string('emergency_contact_phone', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilots', function (Blueprint $table) {
            $table->date('birth_date')->nullable(false)->change();
            $table->string('birth_place')->nullable(false)->change();
            $table->string('address', 500)->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->string('postal_code', 10)->nullable(false)->change();
            $table->string('emergency_contact_name')->nullable(false)->change();
            $table->string('emergency_contact_phone', 20)->nullable(false)->change();
        });
    }
};
