<?php

namespace App\Console\Commands;

use App\Models\PaddockSpot;
use Illuminate\Console\Command;

class SeedPaddockSpots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paddock:seed {--reset : Supprimer tous les emplacements existants avant de créer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer les 90 emplacements de paddock avec zones A, B, C';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('reset')) {
            $this->info('Suppression des emplacements existants...');
            PaddockSpot::query()->delete();
        }

        // Configuration des zones (basé sur le plan fourni)
        // Zone A: Emplacements 1-30 (côté gauche)
        // Zone B: Emplacements 31-60 (zone centrale/piste)
        // Zone C: Emplacements 61-90 (côté droit)

        $zones = [
            'A' => ['count' => 30, 'prefix' => 'A'],
            'B' => ['count' => 30, 'prefix' => 'B'],
            'C' => ['count' => 30, 'prefix' => 'C'],
        ];

        $totalCreated = 0;
        $bar = $this->output->createProgressBar(90);
        $bar->start();

        foreach ($zones as $zoneLetter => $config) {
            for ($i = 1; $i <= $config['count']; $i++) {
                $spotNumber = $config['prefix'].$i;

                // Calculer position approximative pour affichage sur plan
                // (Ces valeurs peuvent être ajustées selon le plan réel)
                $position = $this->calculatePosition($zoneLetter, $i, $config['count']);

                PaddockSpot::create([
                    'spot_number' => $spotNumber,
                    'zone' => $zoneLetter,
                    'position_x' => $position['x'],
                    'position_y' => $position['y'],
                    'is_available' => true,
                    'notes' => $this->generateNotes($zoneLetter, $i),
                ]);

                $totalCreated++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ {$totalCreated} emplacements créés avec succès !");
        $this->table(
            ['Zone', 'Emplacements', 'Numéros'],
            [
                ['A', 30, 'A1 → A30'],
                ['B', 30, 'B1 → B30'],
                ['C', 30, 'C1 → C30'],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Calculer la position approximative d'un emplacement sur le plan
     */
    protected function calculatePosition(string $zone, int $number, int $totalInZone): array
    {
        // Positions approximatives basées sur un plan de 1000x800 pixels
        // Ces valeurs sont indicatives et peuvent être ajustées selon le plan réel

        $positions = [
            'A' => [
                'base_x' => 100,  // Côté gauche
                'base_y' => 100,
                'spacing_x' => 80,
                'spacing_y' => 80,
                'cols' => 5, // 5 colonnes
            ],
            'B' => [
                'base_x' => 400,  // Zone centrale
                'base_y' => 100,
                'spacing_x' => 80,
                'spacing_y' => 80,
                'cols' => 6, // 6 colonnes
            ],
            'C' => [
                'base_x' => 750,  // Côté droit
                'base_y' => 100,
                'spacing_x' => 80,
                'spacing_y' => 80,
                'cols' => 5, // 5 colonnes
            ],
        ];

        $config = $positions[$zone];

        // Calculer ligne et colonne
        $row = floor(($number - 1) / $config['cols']);
        $col = ($number - 1) % $config['cols'];

        return [
            'x' => $config['base_x'] + ($col * $config['spacing_x']),
            'y' => $config['base_y'] + ($row * $config['spacing_y']),
        ];
    }

    /**
     * Générer des notes descriptives pour certains emplacements
     */
    protected function generateNotes(string $zone, int $number): ?string
    {
        // Ajouter des notes pour des emplacements spécifiques
        // (peut être personnalisé selon les besoins réels)

        if ($zone === 'A' && $number === 1) {
            return 'Près de l\'entrée principale';
        }

        if ($zone === 'B' && in_array($number, [1, 15, 30])) {
            return 'Accès direct à la piste';
        }

        if ($zone === 'C' && $number === 30) {
            return 'Près des sanitaires';
        }

        return null;
    }
}
