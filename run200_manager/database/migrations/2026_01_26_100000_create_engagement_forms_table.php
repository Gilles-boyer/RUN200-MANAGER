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
        Schema::create('engagement_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_registration_id')->constrained()->cascadeOnDelete();

            // Signature numérique (stockée en base64)
            $table->longText('signature_data');

            // Informations au moment de la signature
            $table->string('pilot_name');
            $table->string('pilot_license_number')->nullable();
            $table->date('pilot_birth_date')->nullable();
            $table->string('pilot_address')->nullable();
            $table->string('pilot_phone')->nullable();

            // Informations voiture
            $table->string('car_make');
            $table->string('car_model');
            $table->string('car_category');
            $table->unsignedInteger('car_race_number');

            // Informations course
            $table->string('race_name');
            $table->date('race_date');
            $table->string('race_location');

            // Tuteur légal (si mineur)
            $table->boolean('is_minor')->default(false);
            $table->string('guardian_name')->nullable();
            $table->string('guardian_license_number')->nullable();
            $table->longText('guardian_signature_data')->nullable();

            // Contrôle administratif
            $table->foreignId('witnessed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('signed_at');
            $table->string('ip_address')->nullable();
            $table->string('device_info')->nullable();

            $table->timestamps();

            // Index
            $table->index('signed_at');
            $table->index('witnessed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engagement_forms');
    }
};
