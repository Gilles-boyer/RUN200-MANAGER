<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->string('slug', 100)->nullable()->unique()->after('name');
        });

        // Generate slugs for existing races
        $races = \App\Models\Race::all();
        foreach ($races as $race) {
            $baseSlug = Str::slug($race->name . '-' . $race->race_date->format('Y-m-d'));
            $slug = $baseSlug;
            $counter = 1;

            while (\App\Models\Race::where('slug', $slug)->where('id', '!=', $race->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $race->update(['slug' => $slug]);
        }

        // Make slug required after populating existing records
        Schema::table('races', function (Blueprint $table) {
            $table->string('slug', 100)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
