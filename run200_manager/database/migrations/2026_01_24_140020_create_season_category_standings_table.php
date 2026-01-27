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
        Schema::create('season_category_standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->foreignId('car_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('pilot_id')->constrained()->onDelete('cascade');
            $table->unsignedSmallInteger('races_count')->default(0);
            $table->unsignedInteger('base_points')->default(0);
            $table->unsignedSmallInteger('bonus_points')->default(0);
            $table->unsignedInteger('total_points')->default(0);
            $table->unsignedSmallInteger('rank')->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            // Contrainte unique : un pilote par catégorie par saison
            $table->unique(['season_id', 'car_category_id', 'pilot_id'], 'season_cat_pilot_unique');

            // Index pour le classement par catégorie
            $table->index(['season_id', 'car_category_id', 'total_points'], 'season_cat_points_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('season_category_standings');
    }
};
