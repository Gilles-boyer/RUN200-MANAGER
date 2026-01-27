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
        Schema::create('season_points_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->unsignedSmallInteger('position_from');
            $table->unsignedSmallInteger('position_to');
            $table->unsignedSmallInteger('points');
            $table->timestamps();

            // Index pour les lookups par saison
            $table->index(['season_id', 'position_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('season_points_rules');
    }
};
