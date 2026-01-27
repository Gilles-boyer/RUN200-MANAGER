<?php

namespace Database\Factories;

use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SeasonPointsRule>
 */
class SeasonPointsRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $positionFrom = fake()->numberBetween(1, 10);

        return [
            'season_id' => Season::factory(),
            'position_from' => $positionFrom,
            'position_to' => $positionFrom + fake()->numberBetween(0, 5),
            'points' => fake()->randomElement([25, 20, 16, 14, 10, 8, 5]),
        ];
    }

    /**
     * Create a rule for 1st place
     */
    public function first(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_from' => 1,
            'position_to' => 1,
            'points' => 25,
        ]);
    }

    /**
     * Create a rule for 2nd place
     */
    public function second(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_from' => 2,
            'position_to' => 2,
            'points' => 20,
        ]);
    }

    /**
     * Create a rule for 3rd place
     */
    public function third(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_from' => 3,
            'position_to' => 3,
            'points' => 16,
        ]);
    }

    /**
     * Create a rule for "others" (7+)
     */
    public function others(): static
    {
        return $this->state(fn (array $attributes) => [
            'position_from' => 7,
            'position_to' => 9999,
            'points' => 5,
        ]);
    }
}
