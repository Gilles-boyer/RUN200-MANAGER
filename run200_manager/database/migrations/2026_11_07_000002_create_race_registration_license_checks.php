<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_registration_license_checks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('race_registration_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('license_number_snapshot', 6);
            $table->string('pilot_first_name_snapshot');
            $table->string('pilot_last_name_snapshot');
            $table->date('race_date_snapshot');
            $table->string('required_license_code_snapshot', 32);
            $table->string('result', 20);
            $table->string('license_type_observed', 32)->nullable();
            $table->string('license_state_observed', 20)->nullable();
            $table->unsignedSmallInteger('license_year_observed')->nullable();
            $table->boolean('identity_matches')->nullable();
            $table->boolean('license_number_matches')->nullable();
            $table->boolean('valid_for_race_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('source', 32);
            $table->string('source_access_status', 20);
            $table->string('reason_code', 40)->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamp('checked_at');
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('revision')->default(1);
            $table->timestamps(6);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_registration_license_checks');
    }
};
