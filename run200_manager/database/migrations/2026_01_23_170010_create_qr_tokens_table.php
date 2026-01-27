<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_registration_id')->unique()->constrained()->cascadeOnDelete();
            $table->char('token_hash', 64)->unique(); // SHA256 hash
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('token_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_tokens');
    }
};
