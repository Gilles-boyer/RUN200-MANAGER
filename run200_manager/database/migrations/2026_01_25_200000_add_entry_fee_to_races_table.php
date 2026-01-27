<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('races', function (Blueprint $table) {
            // Prix d'inscription en centimes (ex: 5000 = 50.00€)
            // Nullable = utilise le prix par défaut de config('stripe.registration_fee_cents')
            $table->unsignedInteger('entry_fee_cents')->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->dropColumn('entry_fee_cents');
        });
    }
};
