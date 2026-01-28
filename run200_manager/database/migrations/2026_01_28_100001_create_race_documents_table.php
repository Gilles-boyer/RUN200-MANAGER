<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('document_categories')->restrictOnDelete();
            $table->uuid('slug')->unique()->comment('UUID anti-enumération pour URL publique');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'ARCHIVED'])->default('DRAFT');
            $table->enum('visibility', ['PUBLIC', 'REGISTERED_ONLY'])->default('PUBLIC');
            $table->unsignedInteger('current_version')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['race_id', 'status']);
            $table->index(['race_id', 'category_id']);
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_documents');
    }
};
