<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Pilot permissions
            'pilot.manage_own_profile',
            'pilot.view_own_profile',
            'car.manage_own',
            'car.view_own',

            // Race permissions
            'race.view_open',
            'race.view_all',
            'race.manage',
            'race.create',
            'race.delete',

            // Registration permissions
            'race_registration.create',
            'race_registration.view_own',
            'race_registration.view_all',
            'race_registration.validate',
            'race_registration.assign_paddock',
            'race_registration.cancel_own',
            'registration.manage', // Walk-in registration permission

            // Checkpoint permissions
            'checkpoint.scan.admin_check',
            'checkpoint.scan.tech_check',
            'checkpoint.scan.entry',
            'checkpoint.scan.bracelet',

            // Tech inspection permissions
            'tech_inspection.manage',
            'tech_inspection.view',

            // Results permissions
            'results.import',
            'results.publish',
            'results.view_published',

            // Championship permissions
            'championship.view',
            'championship.rebuild',
            'championship.manage',

            // Payment permissions
            'payment.view',
            'payment.manage',
            'payment.refund',

            // Season permissions
            'season.manage',
            'season.view',

            // Category permissions
            'car_category.manage',
            'car_category.view',

            // Admin permissions
            'admin.access',
            'admin.manage_users',
            'admin.view_logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions

        // 1. PILOTE - Can manage own profile, cars, and registrations
        $pilotRole = Role::firstOrCreate(['name' => 'PILOTE', 'guard_name' => 'web']);
        $pilotRole->givePermissionTo([
            'pilot.manage_own_profile',
            'pilot.view_own_profile',
            'car.manage_own',
            'car.view_own',
            'race.view_open',
            'race_registration.create',
            'race_registration.view_own',
            'race_registration.cancel_own',
            'results.view_published',
            'championship.view',
        ]);

        // 2. STAFF_ADMINISTRATIF - Administrative validation
        $staffAdminRole = Role::firstOrCreate(['name' => 'STAFF_ADMINISTRATIF', 'guard_name' => 'web']);
        $staffAdminRole->givePermissionTo([
            'race.view_all',
            'race_registration.view_all',
            'race_registration.validate',
            'race_registration.assign_paddock',
            'checkpoint.scan.admin_check',
            'results.view_published',
            'payment.view',
            'payment.manage',
            'registration.manage', // Walk-in registration
        ]);

        // 3. CONTROLEUR_TECHNIQUE - Technical inspection
        $techControllerRole = Role::firstOrCreate(['name' => 'CONTROLEUR_TECHNIQUE', 'guard_name' => 'web']);
        $techControllerRole->givePermissionTo([
            'race.view_all',
            'race_registration.view_all',
            'checkpoint.scan.tech_check',
            'tech_inspection.manage',
            'tech_inspection.view',
        ]);

        // 4. STAFF_ENTREE - Entry checkpoint
        $staffEntreeRole = Role::firstOrCreate(['name' => 'STAFF_ENTREE', 'guard_name' => 'web']);
        $staffEntreeRole->givePermissionTo([
            'race.view_all',
            'race_registration.view_all',
            'checkpoint.scan.entry',
        ]);

        // 5. STAFF_SONO - Bracelet distribution
        $staffSonoRole = Role::firstOrCreate(['name' => 'STAFF_SONO', 'guard_name' => 'web']);
        $staffSonoRole->givePermissionTo([
            'race.view_all',
            'race_registration.view_all',
            'checkpoint.scan.bracelet',
        ]);

        // 6. ADMIN - Full access
        $adminRole = Role::firstOrCreate(['name' => 'ADMIN', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());

        $this->command->info('✅ Roles and permissions seeded successfully!');
        $this->command->info('   - 6 roles created');
        $this->command->info('   - '.count($permissions).' permissions created');
    }
}
