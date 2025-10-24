<?php
// database/seeders/SubjectsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('subjects')->insert([
            [
                'subject_code' => 'PROG101',
                'subject_name' => 'Programación Básica',
                'credits' => 4,
                'status' => 'active'
            ],
            [
                'subject_code' => 'WEB202',
                'subject_name' => 'Desarrollo Web',
                'credits' => 5,
                'status' => 'active'
            ],
            [
                'subject_code' => 'DATA301',
                'subject_name' => 'Ciencia de Datos',
                'credits' => 6,
                'status' => 'active'
            ],
            [
                'subject_code' => 'DB402',
                'subject_name' => 'Bases de Datos',
                'credits' => 4,
                'status' => 'active'
            ],
        ]);
    }
}