<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create payment permissions
        $paymentPermissions = [
            'payment.view',
            'payment.manage',
            'payment.refund',
        ];

        foreach ($paymentPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // Give payment permissions to STAFF_ADMINISTRATIF (only if role exists)
        try {
            $staffAdminRole = Role::findByName('STAFF_ADMINISTRATIF', 'web');
            $staffAdminRole->givePermissionTo(['payment.view', 'payment.manage']);
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            // Role will be created by seeder
        }

        // Give all payment permissions to ADMIN (only if role exists)
        try {
            $adminRole = Role::findByName('ADMIN', 'web');
            $adminRole->givePermissionTo($paymentPermissions);
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            // Role will be created by seeder
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionsToRemove = ['payment.view', 'payment.manage', 'payment.refund'];

        foreach ($permissionsToRemove as $permissionName) {
            try {
                $permission = Permission::findByName($permissionName, 'web');
                $permission->delete();
            } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                // Permission doesn't exist
            }
        }
    }
};
