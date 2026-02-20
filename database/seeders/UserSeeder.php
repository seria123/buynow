<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Colman Mwakio',
                'username' => 'mwakio',
                'email' => 'mwakio@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'Super Admin',
            ],
            [
                'name' => 'Robert Ochieng',
                'username' => 'Bob Developer',
                'email' => 'robarangs57@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'Developer',
            ],
            [
                'name' => 'Anthony Munene',
                'username' => 'munene',
                'email' => 'munene@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'Operator',
            ],
            [
                'name' => 'Stephen Thuku',
                'username' => 'stephen',
                'email' => 'stephen@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'Developer',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate([
                'email' => $userData['email']
            ], [
                'name' => $userData['name'],
                'username' => $userData['username'] ?? null,
                'password' => $userData['password'],
                'email_verified_at' => now(), // Mark email as verified
            ]);
            $user->assignRole($userData['role']);
        }
    }
}
