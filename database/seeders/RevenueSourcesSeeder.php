<?php
// database/seeders/RevenueSourcesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueSourcesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('revenue_sources')->insert([
            [
                'name' => 'Matrículas',
                'description' => 'Ingresos por concepto de matrículas estudiantiles',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Mensualidades',
                'description' => 'Ingresos por mensualidades de cursos',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Certificaciones',
                'description' => 'Ingresos por emisión de certificados',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Cursos Corporativos',
                'description' => 'Ingresos por cursos para empresas',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}