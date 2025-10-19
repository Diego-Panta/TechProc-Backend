<?php
// database/seeders/UsersSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Estudiantes (más estudiantes para testing)
            [
                'first_name' => 'Juan',
                'last_name' => 'Pérez',
                'full_name' => 'Juan Pérez',
                'dni' => '12345678',
                'document' => '12345678',
                'email' => 'juan.perez@email.com',
                'phone_number' => '+51987654321',
                'password' => Hash::make('password123'),
                'gender' => 'male',
                'country' => 'Perú',
                'role' => json_encode(['student']),
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'María',
                'last_name' => 'García',
                'full_name' => 'María García',
                'dni' => '87654321',
                'document' => '87654321',
                'email' => 'maria.garcia@email.com',
                'phone_number' => '+51987654322',
                'password' => Hash::make('password123'),
                'gender' => 'female',
                'country' => 'Perú',
                'role' => json_encode(['student']),
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'López',
                'full_name' => 'Carlos López',
                'dni' => '23456789',
                'document' => '23456789',
                'email' => 'carlos.lopez@email.com',
                'phone_number' => '+51987654328',
                'password' => Hash::make('password123'),
                'gender' => 'male',
                'country' => 'Perú',
                'role' => json_encode(['student']),
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Martínez',
                'full_name' => 'Ana Martínez',
                'dni' => '34567890',
                'document' => '34567890',
                'email' => 'ana.martinez@email.com',
                'phone_number' => '+51987654329',
                'password' => Hash::make('password123'),
                'gender' => 'female',
                'country' => 'Perú',
                'role' => json_encode(['student']),
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Luis',
                'last_name' => 'González',
                'full_name' => 'Luis González',
                'dni' => '45678901',
                'document' => '45678901',
                'email' => 'luis.gonzalez@email.com',
                'phone_number' => '+51987654330',
                'password' => Hash::make('password123'),
                'gender' => 'male',
                'country' => 'Perú',
                'role' => json_encode(['student']),
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Instructores
            [
                'first_name' => 'Roberto',
                'last_name' => 'Rodríguez',
                'full_name' => 'Roberto Rodríguez',
                'dni' => '11223344',
                'document' => '11223344',
                'email' => 'roberto.rodriguez@email.com',
                'phone_number' => '+51987654323',
                'password' => Hash::make('password123'),
                'gender' => 'male',
                'country' => 'Perú',
                'role' => json_encode(['instructor']),
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Laura',
                'last_name' => 'Silva',
                'full_name' => 'Laura Silva',
                'dni' => '22334455',
                'document' => '22334455',
                'email' => 'laura.silva@email.com',
                'phone_number' => '+51987654325',
                'password' => Hash::make('password123'),
                'gender' => 'female',
                'country' => 'Perú',
                'role' => json_encode(['instructor']),
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Empleados/Técnicos
            [
                'first_name' => 'Ana',
                'last_name' => 'López',
                'full_name' => 'Ana López',
                'dni' => '44332211',
                'document' => '44332211',
                'email' => 'ana.lopez@email.com',
                'phone_number' => '+51987654324',
                'password' => Hash::make('password123'),
                'gender' => 'female',
                'country' => 'Perú',
                'role' => json_encode(['employee', 'technician']),
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Pedro',
                'last_name' => 'Gómez',
                'full_name' => 'Pedro Gómez',
                'dni' => '55443322',
                'document' => '55443322',
                'email' => 'pedro.gomez@email.com',
                'phone_number' => '+51987654326',
                'password' => Hash::make('password123'),
                'gender' => 'male',
                'country' => 'Perú',
                'role' => json_encode(['employee', 'technician']),
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Lucía',
                'last_name' => 'Ramírez',
                'full_name' => 'Lucía Ramírez',
                'dni' => '66554433',
                'document' => '66554433',
                'email' => 'lucia.ramirez@email.com',
                'phone_number' => '+51987654327',
                'password' => Hash::make('password123'),
                'gender' => 'female',
                'country' => 'Perú',
                'role' => json_encode(['employee', 'technician']),
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        DB::table('users')->insert($users);
        
        // Mostrar estadísticas
        $studentCount = collect($users)->filter(function ($user) {
            $roles = json_decode($user['role'], true);
            return is_array($roles) && in_array('student', $roles);
        })->count();
        
        $instructorCount = collect($users)->filter(function ($user) {
            $roles = json_decode($user['role'], true);
            return is_array($roles) && in_array('instructor', $roles);
        })->count();
        
        $employeeCount = collect($users)->filter(function ($user) {
            $roles = json_decode($user['role'], true);
            return is_array($roles) && in_array('employee', $roles);
        })->count();

        $this->command->info('Usuarios creados: ' . count($users));
        $this->command->info(' - Estudiantes: ' . $studentCount);
        $this->command->info(' - Instructores: ' . $instructorCount);
        $this->command->info(' - Empleados: ' . $employeeCount);
    }
}