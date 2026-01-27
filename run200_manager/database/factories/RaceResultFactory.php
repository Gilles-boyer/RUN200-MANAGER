<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\RaceResult;
use App\Models\ResultImport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaceResult>
 */
final class RaceResultFactory extends Factory
{
    protected $model = RaceResult::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $minutes = $this->faker->numberBetween(2, 10);
        $seconds = $this->faker->numberBetween(0, 59);
        $milliseconds = $this->faker->numberBetween(0, 999);

        $timeMs = ($minutes * 60 * 1000) + ($seconds * 1000) + $milliseconds;
        $rawTime = sprintf('%d:%02d.%03d', $minutes, $seconds, $milliseconds);

        return [
            'race_id' => Race::factory(),
            'race_registration_id' => null,
            'result_import_id' => null,
            'position' => $this->faker->unique()->numberBetween(1, 100),
            'bib' => $this->faker->unique()->numberBetween(1, 999),
            'raw_time' => $rawTime,
            'time_ms' => $timeMs,
            'pilot_name' => $this->faker->name(),
            'car_description' => $this->faker->word().' '.$this->faker->colorName(),
            'category_name' => $this->faker->randomElement(['Berline', 'SUV', 'Sport', 'Compact']),
        ];
    }

    /**
     * Set the result for a specific race.
     */
    public function forRace(Race $race): static
    {
        return $this->state(fn (array $attributes) => [
            'race_id' => $race->id,
        ]);
    }

    /**
     * Set the result for a specific registration.
     */
    public function forRegistration(RaceRegistration $registration): static
    {
        return $this->state(fn (array $attributes) => [
            'race_id' => $registration->race_id,
            'race_registration_id' => $registration->id,
            'bib' => $registration->car->race_number,
            'pilot_name' => $registration->pilot->full_name,
            'car_description' => $registration->car->description ?? $registration->car->model,
            'category_name' => $registration->car->category->name ?? null,
        ]);
    }

    /**
     * Link to a specific import.
     */
    public function fromImport(ResultImport $import): static
    {
        return $this->state(fn (array $attributes) => [
            'race_id' => $import->race_id,
            'result_import_id' => $import->id,
        ]);
    }

    /**
     * Set a specific position.
     */
    public function position(int $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }

    /**
     * Set a specific time in milliseconds.
     */
    public function timeMs(int $milliseconds): static
    {
        $totalSeconds = intdiv($milliseconds, 1000);
        $ms = $milliseconds % 1000;
        $minutes = intdiv($totalSeconds, 60);
        $seconds = $totalSeconds % 60;

        return $this->state(fn (array $attributes) => [
            'time_ms' => $milliseconds,
            'raw_time' => sprintf('%d:%02d.%03d', $minutes, $seconds, $ms),
        ]);
    }

    /**
     * Create a podium result (top 3).
     */
    public function podium(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $this->faker->numberBetween(1, 3),
        ]);
    }

    /**
     * Create a first place result.
     */
    public function winner(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => 1,
        ]);
    }
}
