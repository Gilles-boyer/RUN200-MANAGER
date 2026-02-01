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
            // Groupe 1 : Racing (5 catégories)
            ['name' => 'Racing GT', 'sort_order' => 10],
            ['name' => 'Racing Prototype', 'sort_order' => 20],
            ['name' => 'Racing Formule', 'sort_order' => 30],
            ['name' => 'Racing Silhouette', 'sort_order' => 40],
            ['name' => 'Racing Barquette', 'sort_order' => 50],

            // Groupe 2 : Turbo (3 catégories)
            ['name' => 'Turbo < 1600cc', 'sort_order' => 60],
            ['name' => 'Turbo 1600-2000cc', 'sort_order' => 70],
            ['name' => 'Turbo > 2000cc', 'sort_order' => 80],

            // Groupe 3 : Berline (4 catégories)
            ['name' => 'Berline < 1600cc', 'sort_order' => 90],
            ['name' => 'Berline 1600-2000cc', 'sort_order' => 100],
            ['name' => 'Berline 2000-3000cc', 'sort_order' => 110],
            ['name' => 'Berline > 3000cc', 'sort_order' => 120],

            // Groupe 4 : Autres (5 catégories)
            ['name' => 'Propulsion Arrière', 'sort_order' => 130],
            ['name' => 'Cabriolet', 'sort_order' => 140],
            ['name' => 'SUV/4x4', 'sort_order' => 150],
            ['name' => 'Électrique/Hybride', 'sort_order' => 160],
            ['name' => 'Ancêtre (> 25 ans)', 'sort_order' => 170],
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
