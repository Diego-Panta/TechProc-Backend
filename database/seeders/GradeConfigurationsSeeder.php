<?php
// database/seeders/GradeConfigurationsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeConfigurationsSeeder extends Seeder
{
    public function run(): void
    {
        $groups = DB::table('groups')->get();

        $configurations = [];

        foreach ($groups as $group) {
            $configurations[] = [
                'group_id' => $group->id,
                'grading_system' => '0-100',
                'max_grade' => 100.00,
                'passing_grade' => 70.00,
                'evaluation_weight' => 100.00,
            ];
        }

        DB::table('grade_configurations')->insert($configurations);
    }
}