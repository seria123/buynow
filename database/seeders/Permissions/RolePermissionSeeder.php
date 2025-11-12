<?php

namespace Database\Seeders\Permissions;

use App\Models\Permissions\Permission;
use App\Models\Permissions\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all roles
        $superAdmin = Role::where('name', 'Super Admin')->first();
        $developer = Role::where('name', 'Developer')->first();
        $admin = Role::where('name', 'Admin')->first();

        // Get all permissions
        $allPermissions = Permission::all();

        // Super Admin and Developer get all permissions
        if ($superAdmin) {
            $superAdmin->syncPermissions($allPermissions);
        }

        if ($developer) {
            $developer->syncPermissions($allPermissions);
        }

        // Admin gets permissions but NOT:
        // - attach permission to role
        // - detach permission from role
        // - detach role from user
        if ($admin) {
            $adminPermissions = $allPermissions->filter(function ($permission) {
                return !in_array($permission->name, [
                    'attach permission to role',
                    'detach permission from role',
                    'detach role from user',
                ]);
            });
            $admin->syncPermissions($adminPermissions);
        }
    }
}

