<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Creator Utama',
                'email' => 'creator@gmail.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CREATOR,
            ],
            [
                'name' => 'Editor Alex',
                'email' => 'editor@gmail.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_EDITOR,
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
