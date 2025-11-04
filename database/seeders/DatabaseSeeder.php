<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            // Sistema de autenticación, roles y permisos
            // IMPORTANTE: El orden es crítico para evitar errores
            // 1. Primero crear permisos
            PermissionsSeeder::class,
            // 2. Luego crear roles (necesitan que los permisos existan)
            RolesSeeder::class,
            // 3. Finalmente crear usuarios (necesitan que los roles existan)
            AdminUserSeeder::class,
        ]);
    }
}
