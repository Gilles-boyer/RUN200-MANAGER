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
        Schema::create('tech_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_registration_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('status', ['OK', 'FAIL'])->default('OK');
            $table->text('notes')->nullable();
            $table->foreignId('inspected_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('inspected_at')->useCurrent();
            $table->timestamps();

            $table->index('inspected_by');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tech_inspections');
    }
};
