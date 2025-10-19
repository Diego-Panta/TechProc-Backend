<?php
// database/seeders/EvaluationsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EvaluationsSeeder extends Seeder
{
    public function run(): void
    {
        $groups = DB::table('groups')->get();
        $users = DB::table('users')->where('role', 'like', '%instructor%')->get();

        $evaluations = [];

        foreach ($groups as $group) {
            $evaluationTypes = ['Exam', 'Quiz', 'Project', 'Assignment', 'Final'];
            
            foreach ($evaluationTypes as $type) {
                $evaluations[] = [
                    'group_id' => $group->id,
                    'title' => $type . ' - ' . $group->name,
                    'evaluation_type' => $type,
                    'start_date' => Carbon::now()->addDays(rand(10, 30)),
                    'end_date' => Carbon::now()->addDays(rand(31, 60)),
                    'duration_minutes' => rand(60, 180),
                    'total_score' => 100.00,
                    'status' => 'Active',
                    'teacher_creator_id' => $users->random()->id,
                ];
            }
        }

        DB::table('evaluations')->insert($evaluations);
    }
}