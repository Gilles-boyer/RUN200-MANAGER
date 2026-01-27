<?php

namespace Database\Factories;

use App\Models\Checkpoint;
use App\Models\CheckpointPassage;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CheckpointPassageFactory extends Factory
{
    protected $model = CheckpointPassage::class;

    public function definition(): array
    {
        return [
            'race_registration_id' => RaceRegistration::factory(),
            'checkpoint_id' => Checkpoint::factory(),
            'scanned_by' => User::factory(),
            'scanned_at' => now(),
            'meta' => null,
        ];
    }

    public function withMeta(array $meta): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => $meta,
        ]);
    }
}
