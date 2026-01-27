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
        Schema::table('engagement_forms', function (Blueprint $table) {
            // Validation administrative (checkpoint ADMIN_CHECK)
            $table->foreignId('admin_validated_by')->nullable()->after('tech_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('admin_validated_at')->nullable()->after('admin_validated_by');
            $table->string('admin_notes')->nullable()->after('admin_validated_at');

            // Index pour les recherches
            $table->index('admin_validated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('engagement_forms', function (Blueprint $table) {
            $table->dropIndex(['admin_validated_at']);
            $table->dropForeign(['admin_validated_by']);
            $table->dropColumn(['admin_validated_by', 'admin_validated_at', 'admin_notes']);
        });
    }
};
