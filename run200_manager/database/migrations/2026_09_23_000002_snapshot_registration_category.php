<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_registrations', function (Blueprint $table) {
            $table->foreignId('car_category_id')->nullable()->after('car_id')
                ->constrained('car_categories')->nullOnDelete();
        });

        DB::statement('UPDATE race_registrations SET car_category_id = (SELECT car_category_id FROM cars WHERE cars.id = race_registrations.car_id) WHERE car_category_id IS NULL');
    }

    public function down(): void
    {
        Schema::table('race_registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('car_category_id');
        });
    }
};
