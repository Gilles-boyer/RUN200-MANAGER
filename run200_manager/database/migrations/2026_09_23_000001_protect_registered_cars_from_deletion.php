<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_registrations', function (Blueprint $table) {
            $table->dropForeign(['car_id']);
            $table->foreign('car_id')->references('id')->on('cars')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('race_registrations', function (Blueprint $table) {
            $table->dropForeign(['car_id']);
            $table->foreign('car_id')->references('id')->on('cars')->cascadeOnDelete();
        });
    }
};
