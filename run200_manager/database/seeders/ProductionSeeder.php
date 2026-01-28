<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Production Seeder
 *
 * Ce seeder crée UNIQUEMENT les données essentielles pour la production :
 * - Rôles et permissions
 * - Catégories de voitures
 * - Checkpoints
 * - Catégories de documents
 * - Compte administrateur principal
 *
 * Usage: php artisan db:seed --class=ProductionSeeder
 */
class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Initialisation de la base de données PRODUCTION...');
        $this->command->newLine();

        // 1. Rôles et permissions (obligatoire)
        $this->command->info('📋 Création des rôles et permissions...');
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Catégories de voitures (obligatoire)
        $this->command->info('🏎️  Création des catégories de voitures...');
        $this->call(CarCategoriesSeeder::class);

        // 3. Checkpoints (optionnel mais utile)
        $this->command->info('🚩 Création des checkpoints...');
        $this->call(CheckpointsSeeder::class);

        // 4. Catégories de documents (obligatoire pour le tableau d'affichage)
        $this->command->info('📁 Création des catégories de documents...');
        $this->call(DocumentCategoriesSeeder::class);

        // 5. Compte administrateur principal
        $this->createAdminAccount();

        $this->command->newLine();
        $this->command->info('✅ Base de données production initialisée avec succès !');
        $this->command->newLine();

        // Afficher les informations de connexion
        $this->command->table(
            ['Information', 'Valeur'],
            [
                ['Email Admin', config('app.admin_email', 'admin@run200.re')],
                ['Mot de passe', '⚠️  Défini dans ADMIN_PASSWORD ou par défaut'],
                ['URL', config('app.url')],
            ]
        );

        $this->command->warn('⚠️  IMPORTANT : Changez le mot de passe admin après la première connexion !');
    }

    /**
     * Créer le compte administrateur principal.
     */
    private function createAdminAccount(): void
    {
        $this->command->info('👤 Création du compte administrateur...');

        $adminEmail = config('app.admin_email', env('ADMIN_EMAIL', 'admin@run200.re'));
        $adminPassword = env('ADMIN_PASSWORD', 'ChangeMeOnFirstLogin!2026');
        $adminName = env('ADMIN_NAME', 'Administrateur RUN200');

        // Vérifier si l'admin existe déjà
        $existingAdmin = User::where('email', $adminEmail)->first();

        if ($existingAdmin) {
            $this->command->warn("   ⚠️  L'administrateur {$adminEmail} existe déjà.");

            // S'assurer qu'il a le rôle ADMIN
            if (! $existingAdmin->hasRole('ADMIN')) {
                $existingAdmin->assignRole('ADMIN');
                $this->command->info('   ✓ Rôle ADMIN assigné.');
            }

            return;
        }

        // Créer le compte admin
        $admin = User::create([
            'name' => $adminName,
            'email' => $adminEmail,
            'password' => Hash::make($adminPassword),
            'email_verified_at' => now(),
        ]);

        // Assigner le rôle ADMIN
        $adminRole = Role::findByName('ADMIN');
        $admin->assignRole($adminRole);

        $this->command->info("   ✓ Administrateur créé : {$adminEmail}");
    }
}
