<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

class RaceRegistrationFactory extends Factory
{
    protected $model = RaceRegistration::class;

    public function definition(): array
    {
        return [
            'race_id' => Race::factory(),
            'pilot_id' => Pilot::factory(),
            'car_id' => Car::factory(),
            'status' => 'PENDING_VALIDATION',
            'reason' => null,
            'paddock' => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ACCEPTED',
            'validated_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PENDING_VALIDATION',
        ]);
    }

    public function refused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'REFUSED',
            'reason' => $this->faker->sentence(),
        ]);
    }

    public function withPaddock(?string $paddock = null): static
    {
        return $this->state(fn (array $attributes) => [
            'paddock' => $paddock ?? 'P'.$this->faker->numberBetween(1, 100),
        ]);
    }
}
