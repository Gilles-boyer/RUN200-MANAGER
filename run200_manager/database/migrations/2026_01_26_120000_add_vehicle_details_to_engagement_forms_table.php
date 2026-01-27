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
            // Pilote - champs additionnels
            $table->string('pilot_email')->nullable()->after('pilot_phone');
            $table->string('pilot_permit_number')->nullable()->after('pilot_email');
            $table->date('pilot_permit_date')->nullable()->after('pilot_permit_number');

            // Voiture - champs additionnels
            $table->unsignedTinyInteger('car_cylinders')->nullable()->after('car_category'); // 4, 5, 6
            $table->enum('car_fuel', ['essence', 'diesel'])->nullable()->after('car_cylinders');
            $table->enum('car_drive', ['2RM', '4RM'])->nullable()->after('car_fuel');
            $table->boolean('car_has_gas')->default(false)->after('car_drive');

            // Contrôle technique
            $table->string('tech_controller_name')->nullable()->after('witnessed_by');
            $table->timestamp('tech_checked_at')->nullable()->after('tech_controller_name');
            $table->text('tech_notes')->nullable()->after('tech_checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('engagement_forms', function (Blueprint $table) {
            $table->dropColumn([
                'pilot_email',
                'pilot_permit_number',
                'pilot_permit_date',
                'car_cylinders',
                'car_fuel',
                'car_drive',
                'car_has_gas',
                'tech_controller_name',
                'tech_checked_at',
                'tech_notes',
            ]);
        });
    }
};
