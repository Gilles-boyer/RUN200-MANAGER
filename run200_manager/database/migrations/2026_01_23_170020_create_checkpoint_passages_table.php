<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkpoint_passages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checkpoint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scanned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('scanned_at')->useCurrent();
            $table->json('meta')->nullable();
            $table->timestamps();

            // Une seule passage par checkpoint par inscription
            $table->unique(['race_registration_id', 'checkpoint_id'], 'unique_registration_checkpoint');

            $table->index('scanned_by');
            $table->index('scanned_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkpoint_passages');
    }
};
