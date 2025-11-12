<?php

namespace Database\Seeders;

use Database\Seeders\Permissions\RoleSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            \Database\Seeders\Permissions\PermissionSeeder::class,
            \Database\Seeders\Permissions\RolePermissionSeeder::class,
            UserSeeder::class
        ]);
    }
}
