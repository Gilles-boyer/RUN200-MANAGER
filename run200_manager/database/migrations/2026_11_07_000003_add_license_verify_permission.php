<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::firstOrCreate(['name' => 'license.verify', 'guard_name' => 'web']);
        foreach (['ADMIN', 'STAFF_ADMINISTRATIF'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web'])->givePermissionTo($permission);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::query()->where('name', 'license.verify')->where('guard_name', 'web')->first()?->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
