<?php
// database/seeders/DepartmentsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departments')->insert([
            ['department_name' => 'Tecnología', 'description' => 'Departamento de desarrollo y soporte técnico'],
            ['department_name' => 'Académico', 'description' => 'Departamento de gestión académica'],
            ['department_name' => 'Finanzas', 'description' => 'Departamento de gestión financiera'],
            ['department_name' => 'Recursos Humanos', 'description' => 'Departamento de gestión de personal'],
        ]);
    }
}