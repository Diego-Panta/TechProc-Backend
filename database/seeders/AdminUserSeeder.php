<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\AuthenticationSessions\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@techproc.com',
            'password' => Hash::make('password123'),
            'fullname' => 'Super Admin',
            'dni' => '00000000',
        ]);

        $superAdmin->assignRole('super_admin');

        $this->command->info('✅ Usuario Super Admin creado exitosamente!');
        $this->command->info('');
        $this->command->info('Credenciales:');
        $this->command->info('  Email: admin@techproc.com');
        $this->command->info('  Password: password123');
        $this->command->info('  Rol: super_admin');
        $this->command->info('');
        $this->command->info('⚠️  IMPORTANTE: Cambia esta contraseña en producción!');
    }
}
