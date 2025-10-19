<?php
// database/seeders/FinalGradesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinalGradesSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->where('role', 'like', '%student%')->get();
        $groups = DB::table('groups')->get();
        $gradeConfigs = DB::table('grade_configurations')->get();

        $finalGrades = [];

        foreach ($users as $user) {
            foreach ($groups as $group) {
                $config = $gradeConfigs->where('group_id', $group->id)->first();
                $finalGrade = rand(50, 100) + (rand(0, 99) / 100);
                $programStatus = $finalGrade >= $config->passing_grade ? 'Passed' : 'Failed';

                $finalGrades[] = [
                    'user_id' => $user->id,
                    'group_id' => $group->id,
                    'configuration_id' => $config->id,
                    'final_grade' => $finalGrade,
                    'partial_average' => $finalGrade - rand(1, 5),
                    'program_status' => $programStatus,
                    'certification_obtained' => $programStatus === 'Passed',
                    'calculation_date' => Carbon::now(),
                ];
            }
        }

        DB::table('final_grades')->insert($finalGrades);
    }
}