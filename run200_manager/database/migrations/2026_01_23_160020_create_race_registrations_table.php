<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pilot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['PENDING_VALIDATION', 'ACCEPTED', 'REFUSED'])->default('PENDING_VALIDATION');
            $table->string('reason')->nullable();
            $table->string('paddock')->nullable();
            $table->timestamps();

            $table->unique(['race_id', 'pilot_id']);
            $table->unique(['race_id', 'car_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_registrations');
    }
};
