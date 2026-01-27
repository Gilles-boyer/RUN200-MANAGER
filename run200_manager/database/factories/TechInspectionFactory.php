<?php

namespace Database\Factories;

use App\Models\RaceRegistration;
use App\Models\TechInspection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TechInspection>
 */
class TechInspectionFactory extends Factory
{
    protected $model = TechInspection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'race_registration_id' => RaceRegistration::factory(),
            'status' => 'OK',
            'notes' => null,
            'inspected_by' => User::factory(),
            'inspected_at' => now(),
        ];
    }

    /**
     * Indicate the inspection passed (OK)
     */
    public function passed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'OK',
            'notes' => null,
        ]);
    }

    /**
     * Indicate the inspection failed
     */
    public function failed(?string $notes = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'FAIL',
            'notes' => $notes ?? $this->faker->sentence(),
        ]);
    }

    /**
     * Set the inspector
     */
    public function inspectedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'inspected_by' => $user->id,
        ]);
    }

    /**
     * Set the registration
     */
    public function forRegistration(RaceRegistration $registration): static
    {
        return $this->state(fn (array $attributes) => [
            'race_registration_id' => $registration->id,
        ]);
    }
}
