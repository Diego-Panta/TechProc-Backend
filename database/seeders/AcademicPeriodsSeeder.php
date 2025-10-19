<?php
// database/seeders/AcademicPeriodsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AcademicPeriodsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('academic_periods')->insert([
            [
                'academic_period_id' => 202401,
                'name' => 'Ciclo 2024-1',
                'start_date' => '2024-01-15',
                'end_date' => '2024-06-15',
                'status' => 'open',
                'created_at' => Carbon::now(),
            ],
            [
                'academic_period_id' => 202402,
                'name' => 'Ciclo 2024-2',
                'start_date' => '2024-07-15',
                'end_date' => '2024-12-15',
                'status' => 'open',
                'created_at' => Carbon::now(),
            ],
        ]);
    }
}