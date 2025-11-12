<?php

namespace Database\Seeders\Permissions;

use App\Models\Permissions\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Permission management
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            
            // Role management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            
            // Role-Permission management
            'attach permission to role',
            'detach permission from role',
            
            // User-Role management
            'attach role to user',
            'detach role from user',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}

