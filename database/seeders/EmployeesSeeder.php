<?php
// database/seeders/EmployeesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeesSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->where('role', 'like', '%employee%')->get();
        $positions = DB::table('positions')->get();
        $departments = DB::table('departments')->get();

        // Verificar que hay usuarios empleados
        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios con rol employee. Creando empleados temporales...');
            
            $employeeUsers = [
                [
                    'first_name' => 'Ana',
                    'last_name' => 'López',
                    'full_name' => 'Ana López',
                    'dni' => '44332211',
                    'document' => '44332211',
                    'email' => 'ana.lopez@email.com',
                    'phone_number' => '+51987654324',
                    'password' => bcrypt('password123'),
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
                    'password' => bcrypt('password123'),
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
                    'password' => bcrypt('password123'),
                    'gender' => 'female',
                    'country' => 'Perú',
                    'role' => json_encode(['employee', 'technician']),
                    'status' => 'active',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            ];

            foreach ($employeeUsers as $userData) {
                $userId = DB::table('users')->insertGetId($userData);
                $users->push((object)['id' => $userId]);
            }
        }

        $employees = [];
        $employeeId = 10000;

        foreach ($users as $index => $user) {
            $position = $positions->random();
            $department = $departments->where('id', $position->department_id)->first();
            
            $employees[] = [
                'employee_id' => $employeeId + $index,
                'hire_date' => Carbon::now()->subYears(rand(1, 5))->subMonths(rand(1, 12)),
                'position_id' => $position->id,
                'department_id' => $department->id,
                'user_id' => $user->id,
                'employment_status' => 'Active',
                'schedule' => 'Lunes a Viernes 9:00-18:00',
                'speciality' => ['Soporte Técnico', 'Desarrollo', 'Administración'][$index % 3],
                'salary' => rand(3000, 8000) + (rand(0, 99) / 100),
                'created_at' => Carbon::now(),
            ];
        }

        DB::table('employees')->insert($employees);
        $this->command->info('Empleados creados: ' . count($employees));
    }
}