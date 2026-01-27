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
        // Table pour gérer les emplacements du paddock
        Schema::create('paddock_spots', function (Blueprint $table) {
            $table->id();
            $table->string('spot_number', 10)->unique(); // Ex: "A1", "B5", "C12"
            $table->string('zone', 10); // Zone du paddock: "A", "B", "C", etc.
            $table->integer('position_x')->nullable(); // Position X sur le plan (pixels)
            $table->integer('position_y')->nullable(); // Position Y sur le plan (pixels)
            $table->boolean('is_available')->default(true);
            $table->text('notes')->nullable(); // Notes sur l'emplacement (ex: "près des sanitaires")
            $table->timestamps();

            $table->index('zone');
            $table->index('is_available');
        });

        // Ajouter la relation à race_registrations
        Schema::table('race_registrations', function (Blueprint $table) {
            $table->foreignId('paddock_spot_id')->nullable()->after('paddock')->constrained('paddock_spots')->nullOnDelete();

            // Index pour optimiser les requêtes
            $table->index('paddock_spot_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('race_registrations', function (Blueprint $table) {
            $table->dropForeign(['paddock_spot_id']);
            $table->dropColumn('paddock_spot_id');
        });

        Schema::dropIfExists('paddock_spots');
    }
};
