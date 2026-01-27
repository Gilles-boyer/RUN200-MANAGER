<?php

namespace Database\Factories;

use App\Models\Race;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

class RaceFactory extends Factory
{
    protected $model = Race::class;

    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'name' => 'Course '.$this->faker->city(),
            'race_date' => $this->faker->dateTimeBetween('+1 week', '+6 months'),
            'location' => $this->faker->city().', '.$this->faker->country(),
            'status' => 'DRAFT',
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'OPEN',
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'CLOSED',
        ]);
    }

    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'RUNNING',
        ]);
    }

    public function resultsReady(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'RESULTS_READY',
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PUBLISHED',
        ]);
    }
}
