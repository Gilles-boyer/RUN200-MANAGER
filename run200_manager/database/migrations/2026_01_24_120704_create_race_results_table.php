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
        Schema::create('race_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();
            $table->foreignId('race_registration_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('result_import_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('position');
            $table->unsignedSmallInteger('bib'); // race_number de la voiture
            $table->string('raw_time', 50)->nullable(); // temps brut du CSV
            $table->unsignedInteger('time_ms')->nullable(); // temps en millisecondes
            $table->string('pilot_name')->nullable(); // snapshot nom pilote
            $table->string('car_description')->nullable(); // snapshot voiture
            $table->string('category_name')->nullable(); // snapshot catégorie
            $table->timestamps();

            $table->unique(['race_id', 'bib']);
            $table->unique(['race_id', 'position']);
            $table->index(['race_id', 'position']);
            $table->index('race_registration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_results');
    }
};
