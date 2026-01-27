<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pilot>
 */
class PilotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'license_number' => fake()->unique()->numerify('######'),
            'birth_date' => fake()->date('Y-m-d', '-18 years'),
            'birth_place' => fake()->city(),
            'phone' => fake()->phoneNumber(),
            'permit_number' => fake()->numerify('##########'),
            'permit_date' => fake()->date('Y-m-d', '-5 years'),
            'address' => fake()->address(),
            'photo_path' => null,
            'is_minor' => false,
            'guardian_first_name' => null,
            'guardian_last_name' => null,
            'guardian_license_number' => null,
        ];
    }

    /**
     * Indicate that the pilot is a minor.
     */
    public function minor(): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => fake()->date('Y-m-d', '-10 years'),
            'is_minor' => true,
        ]);
    }

    /**
     * Indicate that the pilot has a guardian.
     */
    public function withGuardian(): static
    {
        return $this->state(function (array $attributes) {
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();

            return [
                'is_minor' => true,
                'guardian_first_name' => $firstName,
                'guardian_last_name' => $lastName,
                'guardian_name' => "$firstName $lastName",
                'guardian_phone' => fake()->phoneNumber(),
                'guardian_license_number' => fake()->numerify('######'),
            ];
        });
    }

    /**
     * Indicate that the pilot profile is complete (all required fields filled).
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'photo_path' => 'pilots/photos/test-photo.jpg',
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->phoneNumber(),
        ]);
    }
}
