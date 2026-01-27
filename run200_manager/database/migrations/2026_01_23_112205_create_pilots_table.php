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
        Schema::create('pilots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('license_number', 6)->unique();
            $table->date('birth_date');
            $table->string('birth_place');
            $table->string('phone', 20);
            $table->string('address', 500);
            $table->string('photo_path')->nullable();
            $table->boolean('is_minor')->default(false);
            $table->string('guardian_first_name')->nullable();
            $table->string('guardian_last_name')->nullable();
            $table->string('guardian_license_number', 6)->nullable();
            $table->string('guardian_name')->nullable(); // Nom complet pour compatibilité
            $table->string('guardian_phone', 20)->nullable();
            $table->boolean('is_active_season')->default(true);
            $table->timestamps();

            $table->index('license_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilots');
    }
};
