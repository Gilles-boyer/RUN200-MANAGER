<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un pilote peut inscrire plusieurs de ses voitures sur la même course.
 * Seule contrainte: une même voiture ne peut pas être inscrite deux fois sur la même course.
 * Cette migration supprime la contrainte d'unicité race_id + pilot_id.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('race_registrations', function (Blueprint $table) {
            $table->dropUnique(['race_id', 'pilot_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('race_registrations', function (Blueprint $table) {
            $table->unique(['race_id', 'pilot_id']);
        });
    }
};
