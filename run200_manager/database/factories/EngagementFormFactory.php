<?php

namespace Database\Factories;

use App\Models\EngagementForm;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EngagementForm>
 */
class EngagementFormFactory extends Factory
{
    protected $model = EngagementForm::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a simple signature data (base64 encoded small image)
        $signatureData = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        return [
            'race_registration_id' => RaceRegistration::factory(),
            'signature_data' => $signatureData,
            'pilot_name' => $this->faker->name(),
            'pilot_license_number' => $this->faker->numerify('######'),
            'pilot_birth_date' => $this->faker->date(),
            'pilot_address' => $this->faker->address(),
            'pilot_phone' => $this->faker->phoneNumber(),
            'car_make' => $this->faker->randomElement(['BMW', 'Audi', 'Peugeot', 'Renault']),
            'car_model' => $this->faker->word(),
            'car_category' => $this->faker->randomElement(['SPORT', 'TOURING', 'GT']),
            'car_race_number' => $this->faker->numberBetween(1, 999),
            'race_name' => 'Course Test '.$this->faker->numberBetween(1, 10),
            'race_date' => $this->faker->date(),
            'race_location' => $this->faker->city(),
            'is_minor' => false,
            'guardian_name' => null,
            'guardian_license_number' => null,
            'guardian_signature_data' => null,
            'witnessed_by' => User::factory(),
            'signed_at' => now(),
            'ip_address' => $this->faker->ipv4(),
            'device_info' => $this->faker->userAgent(),
        ];
    }

    /**
     * Indicate that the pilot is a minor.
     */
    public function minor(): static
    {
        $signatureData = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        return $this->state(fn (array $attributes) => [
            'is_minor' => true,
            'guardian_name' => $this->faker->name(),
            'guardian_license_number' => $this->faker->numerify('######'),
            'guardian_signature_data' => $signatureData,
        ]);
    }
}
