<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'department_name' => 'Desarrollo Web',
                'description' => 'Departamento encargado del desarrollo y mantenimiento de aplicaciones web'
            ],
            [
                'department_name' => 'Soporte Técnico',
                'description' => 'Departamento de atención al cliente y soporte técnico'
            ],
            [
                'department_name' => 'Administración',
                'description' => 'Departamento administrativo y de gestión'
            ],
            [
                'department_name' => 'Marketing',
                'description' => 'Departamento de marketing y comunicaciones'
            ]
        ];

        foreach ($departments as $department) {
            DB::table('departments')->insert($department);
        }
    }
}