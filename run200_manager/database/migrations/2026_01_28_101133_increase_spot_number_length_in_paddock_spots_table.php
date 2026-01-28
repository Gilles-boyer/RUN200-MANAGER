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
        Schema::table('paddock_spots', function (Blueprint $table) {
            // Augmenter la taille de spot_number de 10 à 20 caractères
            // pour supporter des noms de zones plus longs (ex: PALMISTE001)
            $table->string('spot_number', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paddock_spots', function (Blueprint $table) {
            $table->string('spot_number', 10)->change();
        });
    }
};
