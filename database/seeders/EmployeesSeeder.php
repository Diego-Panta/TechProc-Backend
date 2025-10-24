<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeesSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->get()->filter(function ($u) {
            $roles = json_decode($u->role, true);
            return is_array($roles) && in_array('employee', $roles);
        });

        $positions = DB::table('positions')->get();
        $departments = DB::table('departments')->get();

        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios con rol employee.');
            return;
        }

        $employees = [];
        $employeeId = 10000;

        foreach ($users as $index => $user) {
            // Buscar posición por defecto o aleatoria
            $position = $positions->random();
            $department = $departments->where('id', $position->department_id)->first();

            // Si el usuario es el desarrollador, personalizamos su perfil
            $speciality = 'Soporte Técnico';
            if (str_contains(strtolower($user->full_name), 'desarrollador')) {
                $speciality = 'Desarrollo Web';
            } elseif ($index % 3 === 1) {
                $speciality = 'Administración';
            }

            $employees[] = [
                'employee_id' => $employeeId + $index,
                'hire_date' => Carbon::now()->subYears(rand(1, 3)),
                'position_id' => $position->id,
                'department_id' => $department->id,
                'user_id' => $user->id,
                'employment_status' => 'Active',
                'schedule' => 'Lunes a Viernes 9:00-18:00',
                'speciality' => $speciality,
                'salary' => rand(3500, 9500) + (rand(0, 99) / 100),
                'created_at' => Carbon::now(),
            ];
        }

        DB::table('employees')->insert($employees);
        $this->command->info('✅ Empleados creados: ' . count($employees));
    }
}
