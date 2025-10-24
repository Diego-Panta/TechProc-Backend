<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            // Desarrollo Web
            [
                'position_name' => 'Desarrollador Web Senior',
                'department_id' => 1
            ],
            [
                'position_name' => 'Desarrollador Web Junior',
                'department_id' => 1
            ],
            [
                'position_name' => 'Diseñador UX/UI',
                'department_id' => 1
            ],
            
            // Soporte Técnico
            [
                'position_name' => 'Especialista en Soporte Técnico',
                'department_id' => 2
            ],
            [
                'position_name' => 'Coordinador de Soporte',
                'department_id' => 2
            ],
            
            // Administración
            [
                'position_name' => 'Gerente Administrativo',
                'department_id' => 3
            ],
            [
                'position_name' => 'Asistente Administrativo',
                'department_id' => 3
            ],
            
            // Marketing
            [
                'position_name' => 'Especialista en Marketing Digital',
                'department_id' => 4
            ]
        ];

        foreach ($positions as $position) {
            DB::table('positions')->insert($position);
        }
    }
}