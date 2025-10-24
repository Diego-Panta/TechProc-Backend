<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Administrator\Models\User;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar si ya existe el usuario
        if (!User::where('email', 'developer@incadev.com')->exists()) {
            User::create([
                'first_name' => 'Admin',
                'last_name' => 'Developer',
                'full_name' => 'Admin Developer',
                'email' => 'developer@incadev.com',
                'password' => Hash::make('password'), // Cambia esto en producción
                'role' => json_encode(['admin']),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('Usuario developer creado exitosamente.');
        }

        // Crear más usuarios si es necesario
        if (!User::where('email', 'content@incadev.com')->exists()) {
            User::create([
                'first_name' => 'Content',
                'last_name' => 'Manager',
                'full_name' => 'Content Manager',
                'email' => 'content@incadev.com',
                'password' => Hash::make('password'),
                'role' => json_encode(['content_manager']),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
        }
    }
}