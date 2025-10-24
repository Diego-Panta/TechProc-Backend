<?php
// database/seeders/GradeRecordsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GradeRecordsSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->where('role', 'like', '%student%')->get();
        $evaluations = DB::table('evaluations')->get();
        $groups = DB::table('groups')->get();
        $gradeConfigs = DB::table('grade_configurations')->get();

        $gradeRecords = [];

        foreach ($users as $user) {
            foreach ($evaluations as $evaluation) {
                $group = $groups->where('id', $evaluation->group_id)->first();
                $config = $gradeConfigs->where('group_id', $group->id)->first();

                $gradeRecords[] = [
                    'user_id' => $user->id,
                    'evaluation_id' => $evaluation->id,
                    'group_id' => $group->id,
                    'configuration_id' => $config->id,
                    'obtained_grade' => rand(50, 100) + (rand(0, 99) / 100),
                    'grade_weight' => $config->evaluation_weight,
                    'grade_type' => ['Partial', 'Final', 'Makeup'][rand(0, 2)],
                    'status' => ['Recorded', 'Validated', 'Published'][rand(0, 2)],
                    'record_date' => Carbon::now()->subDays(rand(1, 30)),
                ];
            }
        }

        DB::table('grade_records')->insert($gradeRecords);
    }
}