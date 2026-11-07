<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('races', function (Blueprint $table): void {
            $table->string('required_license_code', 32)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('races', function (Blueprint $table): void {
            $table->dropColumn('required_license_code');
        });
    }
};
