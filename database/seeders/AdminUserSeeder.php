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
            'name' => 'super_admin',
            'email' => 'admin@techproc.com',
            'password' => Hash::make('password123'),
            'fullname' => 'Super Admin',
            'dni' => '00000000',
        ]);

        // Asignar rol usando el guard correcto
        $role = \Spatie\Permission\Models\Role::where('name', 'super_admin')->where('guard_name', 'web')->first();
        $superAdmin->assignRole($role);

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
