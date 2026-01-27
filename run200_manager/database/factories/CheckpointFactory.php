<?php

namespace Database\Factories;

use App\Models\Checkpoint;
use Illuminate\Database\Eloquent\Factories\Factory;

class CheckpointFactory extends Factory
{
    protected $model = Checkpoint::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->word()),
            'name' => $this->faker->sentence(3),
            'required_permission' => null,
            'sort_order' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
        ];
    }

    public function adminCheck(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'ADMIN_CHECK',
            'name' => 'Vérification administrative',
            'required_permission' => 'checkpoint.scan.admin_check',
            'sort_order' => 1,
        ]);
    }

    public function techCheck(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'TECH_CHECK',
            'name' => 'Vérification technique',
            'required_permission' => 'checkpoint.scan.tech_check',
            'sort_order' => 2,
        ]);
    }

    public function entry(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'ENTRY',
            'name' => 'Entrée pilote et voiture',
            'required_permission' => 'checkpoint.scan.entry',
            'sort_order' => 3,
        ]);
    }

    public function bracelet(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'BRACELET',
            'name' => 'Remise bracelet pilote',
            'required_permission' => 'checkpoint.scan.bracelet',
            'sort_order' => 4,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
