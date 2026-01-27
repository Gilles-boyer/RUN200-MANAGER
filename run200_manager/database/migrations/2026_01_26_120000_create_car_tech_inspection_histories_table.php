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
        Schema::create('car_tech_inspection_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('race_registration_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tech_inspection_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['OK', 'FAIL'])->default('OK');
            $table->text('notes')->nullable();
            $table->json('inspection_details')->nullable(); // Détails techniques structurés
            $table->foreignId('inspected_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('inspected_at');
            $table->timestamps();

            // Indexes pour recherche et performance
            $table->index('car_id');
            $table->index('inspected_by');
            $table->index('status');
            $table->index('inspected_at');
            $table->index(['car_id', 'inspected_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_tech_inspection_histories');
    }
};
