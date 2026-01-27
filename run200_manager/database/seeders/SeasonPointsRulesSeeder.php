<?php

namespace Database\Seeders;

use App\Domain\Championship\Rules\PointsTable;
use App\Models\Season;
use App\Models\SeasonPointsRule;
use Illuminate\Database\Seeder;

class SeasonPointsRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates default points rules for all seasons that don't have rules yet.
     */
    public function run(): void
    {
        $seasons = Season::doesntHave('pointsRules')->get();

        foreach ($seasons as $season) {
            $this->createRulesForSeason($season);
        }

        // If no seasons exist, just display info
        if ($seasons->isEmpty()) {
            $this->command->info('No seasons without points rules found. Skipping.');
        } else {
            $this->command->info("Created default points rules for {$seasons->count()} season(s).");
        }
    }

    /**
     * Create default points rules for a season.
     */
    public function createRulesForSeason(Season $season): void
    {
        $rules = PointsTable::getDefaultRulesForSeeding();

        foreach ($rules as $rule) {
            SeasonPointsRule::create([
                'season_id' => $season->id,
                'position_from' => $rule['position_from'],
                'position_to' => $rule['position_to'],
                'points' => $rule['points'],
            ]);
        }
    }

    /**
     * Get the default barème as a formatted table for display.
     */
    public static function getBaremeTable(): array
    {
        return [
            ['Position', 'Points'],
            ['1er', '25'],
            ['2ème', '20'],
            ['3ème', '16'],
            ['4ème', '14'],
            ['5ème', '10'],
            ['6ème', '8'],
            ['7ème et +', '5'],
        ];
    }
}
