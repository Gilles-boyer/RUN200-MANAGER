<?php

namespace Database\Factories;

use App\Models\CarCategory;
use App\Models\Pilot;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SeasonCategoryStanding>
 */
class SeasonCategoryStandingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $racesCount = fake()->numberBetween(0, 5);
        $basePoints = $racesCount * fake()->numberBetween(5, 25);
        $bonusPoints = $racesCount >= 3 ? 20 : 0;

        return [
            'season_id' => Season::factory(),
            'car_category_id' => CarCategory::factory(),
            'pilot_id' => Pilot::factory(),
            'races_count' => $racesCount,
            'base_points' => $basePoints,
            'bonus_points' => $bonusPoints,
            'total_points' => $basePoints + $bonusPoints,
            'rank' => $racesCount >= 2 ? fake()->numberBetween(1, 20) : null,
            'computed_at' => now(),
        ];
    }

    /**
     * Create a standing with rank (eligible)
     */
    public function ranked(int $rank = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'races_count' => max(2, $attributes['races_count']),
            'rank' => $rank,
        ]);
    }

    /**
     * Create a standing without rank (not eligible)
     */
    public function unranked(): static
    {
        return $this->state(fn (array $attributes) => [
            'races_count' => 1,
            'rank' => null,
        ]);
    }

    /**
     * Create a standing with bonus
     */
    public function withBonus(): static
    {
        return $this->state(fn (array $attributes) => [
            'bonus_points' => 20,
            'total_points' => $attributes['base_points'] + 20,
        ]);
    }

    /**
     * Create a category champion (1st place)
     */
    public function categoryChampion(): static
    {
        return $this->state(fn (array $attributes) => [
            'races_count' => 5,
            'base_points' => 125,
            'bonus_points' => 20,
            'total_points' => 145,
            'rank' => 1,
        ]);
    }
}
