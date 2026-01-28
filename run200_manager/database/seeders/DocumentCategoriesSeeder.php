<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;

class DocumentCategoriesSeeder extends Seeder
{
    /**
     * Catégories de documents officiels pour le tableau d'affichage
     * Basées sur les exigences réglementaires des courses automobiles FFSA/LSAR
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Règlement particulier',
                'slug' => 'reglement-particulier',
                'description' => 'Règlement spécifique de la course',
                'is_required' => true,
                'is_multiple' => false,
                'sort_order' => 10,
            ],
            [
                'name' => 'Assurance',
                'slug' => 'assurance',
                'description' => 'Attestation d\'assurance de la manifestation',
                'is_required' => true,
                'is_multiple' => false,
                'sort_order' => 20,
            ],
            [
                'name' => 'Arrêté préfectoral',
                'slug' => 'arrete-prefectoral',
                'description' => 'Arrêté de manifestation de la préfecture',
                'is_required' => true,
                'is_multiple' => false,
                'sort_order' => 30,
            ],
            [
                'name' => 'Visa FFSA',
                'slug' => 'visa-ffsa',
                'description' => 'Visa de la Fédération Française du Sport Automobile',
                'is_required' => true,
                'is_multiple' => false,
                'sort_order' => 40,
            ],
            [
                'name' => 'Visa LSAR',
                'slug' => 'visa-lsar',
                'description' => 'Visa de la Ligue du Sport Automobile de La Réunion',
                'is_required' => false,
                'is_multiple' => false,
                'sort_order' => 50,
            ],
            [
                'name' => 'Liste des engagés',
                'slug' => 'liste-engages',
                'description' => 'Liste officielle des pilotes engagés',
                'is_required' => false,
                'is_multiple' => false,
                'sort_order' => 60,
            ],
            [
                'name' => 'Programme',
                'slug' => 'programme',
                'description' => 'Programme horaire de la manifestation',
                'is_required' => false,
                'is_multiple' => false,
                'sort_order' => 70,
            ],
            [
                'name' => 'Additif',
                'slug' => 'additif',
                'description' => 'Bulletins modificatifs au règlement (numérotés)',
                'is_required' => false,
                'is_multiple' => true, // Peut avoir plusieurs additifs
                'sort_order' => 80,
            ],
            [
                'name' => 'Plan du parcours',
                'slug' => 'plan-parcours',
                'description' => 'Plan ou tracé du parcours',
                'is_required' => false,
                'is_multiple' => false,
                'sort_order' => 90,
            ],
            [
                'name' => 'Résultats officiels',
                'slug' => 'resultats-officiels',
                'description' => 'Classements et résultats officiels',
                'is_required' => false,
                'is_multiple' => true, // Plusieurs classements possibles
                'sort_order' => 100,
            ],
            [
                'name' => 'Autre document',
                'slug' => 'autre',
                'description' => 'Autres documents officiels',
                'is_required' => false,
                'is_multiple' => true,
                'sort_order' => 999,
            ],
        ];

        foreach ($categories as $category) {
            DocumentCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
