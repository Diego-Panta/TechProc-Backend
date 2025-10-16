<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'user_id' => 1, // Juan Pérez
                'employee_id' => 1001,
                'hire_date' => now()->subYears(3),
                'position_id' => 1, // Desarrollador Web Senior
                'department_id' => 1, // Desarrollo Web
                'employment_status' => 'Active',
                'speciality' => 'Desarrollo Full Stack',
                'salary' => 75000.00,
                'created_at' => now()
            ],
            [
                'user_id' => 2, // María García
                'employee_id' => 1002,
                'hire_date' => now()->subYears(1),
                'position_id' => 2, // Desarrollador Web Junior
                'department_id' => 1, // Desarrollo Web
                'employment_status' => 'Active',
                'speciality' => 'Frontend Development',
                'salary' => 45000.00,
                'created_at' => now()
            ],
            [
                'user_id' => 3, // Carlos Rodríguez
                'employee_id' => 1003,
                'hire_date' => now()->subYears(2),
                'position_id' => 4, // Especialista en Soporte Técnico
                'department_id' => 2, // Soporte Técnico
                'employment_status' => 'Active',
                'speciality' => 'Soporte Técnico',
                'salary' => 40000.00,
                'created_at' => now()
            ],
            [
                'user_id' => 4, // Ana López
                'employee_id' => 1004,
                'hire_date' => now()->subYears(4),
                'position_id' => 6, // Gerente Administrativo
                'department_id' => 3, // Administración
                'employment_status' => 'Active',
                'speciality' => 'Gestión Administrativa',
                'salary' => 60000.00,
                'created_at' => now()
            ]
        ];

        foreach ($employees as $employee) {
            DB::table('employees')->insert($employee);
        }
    }
}