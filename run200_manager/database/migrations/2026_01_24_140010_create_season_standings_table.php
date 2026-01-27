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
        Schema::create('season_standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->foreignId('pilot_id')->constrained()->onDelete('cascade');
            $table->unsignedSmallInteger('races_count')->default(0);
            $table->unsignedInteger('base_points')->default(0);
            $table->unsignedSmallInteger('bonus_points')->default(0);
            $table->unsignedInteger('total_points')->default(0);
            $table->unsignedSmallInteger('rank')->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            // Contrainte unique : un pilote par saison
            $table->unique(['season_id', 'pilot_id']);

            // Index pour le classement
            $table->index(['season_id', 'total_points', 'rank']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('season_standings');
    }
};
