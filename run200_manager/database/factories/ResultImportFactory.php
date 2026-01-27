<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Race;
use App\Models\ResultImport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResultImport>
 */
final class ResultImportFactory extends Factory
{
    protected $model = ResultImport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'race_id' => Race::factory(),
            'uploaded_by' => User::factory(),
            'original_filename' => $this->faker->word().'_results.csv',
            'stored_path' => 'imports/'.$this->faker->uuid().'.csv',
            'row_count' => $this->faker->numberBetween(5, 50),
            'status' => 'PENDING',
            'errors' => null,
        ];
    }

    /**
     * Set the import as pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PENDING',
            'errors' => null,
        ]);
    }

    /**
     * Set the import as imported successfully.
     */
    public function imported(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'IMPORTED',
            'errors' => null,
        ]);
    }

    /**
     * Set the import as failed with errors.
     */
    public function failed(array $errors = []): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'FAILED',
            'errors' => $errors ?: [
                ['row' => 2, 'message' => 'Invalid bib number: 999'],
                ['row' => 5, 'message' => 'Duplicate bib: 42'],
            ],
        ]);
    }

    /**
     * Set a specific race.
     */
    public function forRace(Race $race): static
    {
        return $this->state(fn (array $attributes) => [
            'race_id' => $race->id,
        ]);
    }

    /**
     * Set a specific uploader.
     */
    public function uploadedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'uploaded_by' => $user->id,
        ]);
    }
}
