<?php

namespace Database\Seeders;

use App\Domains\Administrator\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuarios para testing
        $users = [
            [
                'first_name' => 'Juan',
                'last_name' => 'Pérez',
                'full_name' => 'Juan Pérez',
                'email' => 'juan.perez@empresa.com',
                'password' => Hash::make('password123'),
                'role' => json_encode(['admin', 'developer']),
                'status' => 'active',
                'phone_number' => '+51 987654321',
                'country' => 'Perú'
            ],
            [
                'first_name' => 'María',
                'last_name' => 'García',
                'full_name' => 'María García',
                'email' => 'maria.garcia@empresa.com',
                'password' => Hash::make('password123'),
                'role' => json_encode(['developer']),
                'status' => 'active',
                'phone_number' => '+51 987654322',
                'country' => 'Perú'
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'Rodríguez',
                'full_name' => 'Carlos Rodríguez',
                'email' => 'carlos.rodriguez@empresa.com',
                'password' => Hash::make('password123'),
                'role' => json_encode(['support']),
                'status' => 'active',
                'phone_number' => '+51 987654323',
                'country' => 'Perú'
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'López',
                'full_name' => 'Ana López',
                'email' => 'ana.lopez@empresa.com',
                'password' => Hash::make('password123'),
                'role' => json_encode(['admin']),
                'status' => 'active',
                'phone_number' => '+51 987654324',
                'country' => 'Perú'
            ]
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}