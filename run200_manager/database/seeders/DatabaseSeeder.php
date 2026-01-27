<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles, permissions, categories and checkpoints first
        $this->call([
            RolesAndPermissionsSeeder::class,
            CarCategoriesSeeder::class,
            CheckpointsSeeder::class,
        ]);

        // Seed demo data
        $this->call(DemoDataSeeder::class);
    }
}
