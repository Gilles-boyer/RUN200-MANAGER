<?php

namespace Database\Seeders;

use App\Models\CarCategory;
use Illuminate\Database\Seeder;

class CarCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Groupe 1 : Diesel
            ['name' => 'Diesel 100% Mécanique', 'sort_order' => 10],
            ['name' => 'Diesel 2rm 4 cylindres', 'sort_order' => 20],
            ['name' => 'Diesel 2rm 4 cylindres gaz', 'sort_order' => 30],
            ['name' => 'Diesel 4rm 4 cylindres', 'sort_order' => 40],
            ['name' => 'Diesel 4rm 4 cylindres gaz', 'sort_order' => 50],
            ['name' => 'Diesel 2rm 6 cylindres', 'sort_order' => 60],
            ['name' => 'Diesel 2rm 6 cylindres gaz', 'sort_order' => 70],
            ['name' => 'Diesel 4rm 6 cylindres', 'sort_order' => 80],
            ['name' => 'Diesel 4rm 6 cylindres gaz', 'sort_order' => 90],

            // Groupe 2 : Essence
            ['name' => 'Essence Youngtimer (205gti, AX, ...)', 'sort_order' => 100],
            ['name' => 'Essence 2rm 4 cylindres', 'sort_order' => 110],
            ['name' => 'Essence 2rm 4 cylindres gaz', 'sort_order' => 120],
            ['name' => 'Essence 4rm 4 cylindres', 'sort_order' => 130],
            ['name' => 'Essence 4rm 4 cylindres gaz', 'sort_order' => 140],
            ['name' => 'Essence 4rm 5 cylindres', 'sort_order' => 150],
            ['name' => 'Essence 2rm 6 cylindres', 'sort_order' => 160],
            ['name' => 'Essence 4rm 6 cylindres', 'sort_order' => 170],
        ];

        $created = 0;
        $existing = 0;

        foreach ($categories as $category) {
            $result = CarCategory::firstOrCreate(
                ['name' => $category['name']],
                [
                    'is_active' => true,
                    'sort_order' => $category['sort_order'],
                ]
            );

            if ($result->wasRecentlyCreated) {
                $created++;
            } else {
                $existing++;
            }
        }

        $this->command->info("✅ Catégories de voitures: {$created} créées, {$existing} existantes.");
    }
}
