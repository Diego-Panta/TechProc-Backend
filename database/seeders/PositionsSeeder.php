<?php
// database/seeders/PositionsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionsSeeder extends Seeder
{
    public function run(): void
    {
        $departments = DB::table('departments')->get();

        DB::table('positions')->insert([
            [
                'position_name' => 'Desarrollador Senior',
                'department_id' => $departments->where('department_name', 'Tecnología')->first()->id
            ],
            [
                'position_name' => 'Soporte Técnico',
                'department_id' => $departments->where('department_name', 'Tecnología')->first()->id
            ],
            [
                'position_name' => 'Coordinador Académico',
                'department_id' => $departments->where('department_name', 'Académico')->first()->id
            ],
            [
                'position_name' => 'Analista Financiero',
                'department_id' => $departments->where('department_name', 'Finanzas')->first()->id
            ],
        ]);
    }
}