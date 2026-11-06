<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seed the roles and permissions listed in config/employees.php.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard');

        foreach (config('employees.permissions') as $permissionName) {
            Permission::findOrCreate($permissionName, $guard);
        }

        foreach (config('employees.roles') as $roleName => $permissionNames) {
            Role::findOrCreate($roleName, $guard)->syncPermissions($permissionNames);
        }
    }
}
