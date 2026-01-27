<?php

namespace Database\Seeders;

use App\Models\Checkpoint;
use Illuminate\Database\Seeder;

class CheckpointsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $checkpoints = [
            [
                'code' => 'ADMIN_CHECK',
                'name' => 'Vérification administrative',
                'required_permission' => 'checkpoint.scan.admin_check',
                'sort_order' => 1,
            ],
            [
                'code' => 'TECH_CHECK',
                'name' => 'Vérification technique',
                'required_permission' => 'checkpoint.scan.tech_check',
                'sort_order' => 2,
            ],
            [
                'code' => 'ENTRY',
                'name' => 'Entrée pilote et voiture',
                'required_permission' => 'checkpoint.scan.entry',
                'sort_order' => 3,
            ],
            [
                'code' => 'BRACELET',
                'name' => 'Remise bracelet pilote',
                'required_permission' => 'checkpoint.scan.bracelet',
                'sort_order' => 4,
            ],
        ];

        foreach ($checkpoints as $checkpoint) {
            Checkpoint::updateOrCreate(
                ['code' => $checkpoint['code']],
                $checkpoint
            );
        }
    }
}
