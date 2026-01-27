<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    private static $usedRaceNumbers = [];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $makes = ['Ferrari', 'Porsche', 'BMW', 'Audi', 'Mercedes', 'Honda', 'Toyota', 'Nissan', 'Subaru', 'Ford', 'Chevrolet', 'Renault', 'Peugeot', 'Citroën'];
        $models = ['GT3', 'RSR', 'M3', 'R8', 'AMG', 'Civic Type R', 'Supra', 'GT-R', 'WRX STI', 'Mustang', 'Camaro', 'Clio RS', '208 GTi', 'DS3 Racing'];

        // Generate unique race number
        do {
            $raceNumber = fake()->numberBetween(0, 999);
        } while (in_array($raceNumber, self::$usedRaceNumbers));

        self::$usedRaceNumbers[] = $raceNumber;

        return [
            'pilot_id' => \App\Models\Pilot::factory(),
            'car_category_id' => \App\Models\CarCategory::factory(),
            'race_number' => $raceNumber,
            'make' => fake()->randomElement($makes),
            'model' => fake()->randomElement($models),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
