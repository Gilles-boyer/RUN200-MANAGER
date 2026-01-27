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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pilot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_category_id')->constrained()->restrictOnDelete();
            $table->smallInteger('race_number')->unique();
            $table->string('make', 100);
            $table->string('model', 100);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('pilot_id');
            $table->index('car_category_id');
            $table->index('race_number');

            // CHECK constraint for race_number range (0-999) would be added here for MySQL/PostgreSQL
            // SQLite doesn't support Blueprint::check() method
            // Range validation is enforced at application level via RaceNumber ValueObject
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
