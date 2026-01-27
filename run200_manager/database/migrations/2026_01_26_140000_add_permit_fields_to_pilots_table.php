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
        Schema::table('pilots', function (Blueprint $table) {
            $table->string('permit_number', 50)->nullable()->after('phone');
            $table->date('permit_date')->nullable()->after('permit_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilots', function (Blueprint $table) {
            $table->dropColumn(['permit_number', 'permit_date']);
        });
    }
};
