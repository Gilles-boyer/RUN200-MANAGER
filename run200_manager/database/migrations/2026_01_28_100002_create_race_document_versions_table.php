<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('race_documents')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('file_path', 500)->comment('Chemin dans le storage');
            $table->string('original_filename', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size')->comment('Taille en bytes');
            $table->char('checksum', 64)->comment('SHA256 du fichier');
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable()->comment('Notes internes sur cette version');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['document_id', 'version'], 'uk_document_version');
            $table->index('document_id');
            $table->index('checksum');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_document_versions');
    }
};
