<?php
// database/seeders/GroupsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GroupsSeeder extends Seeder
{
    public function run(): void
    {
        $courses = DB::table('courses')->get();

        $groups = [];
        $groupCodes = ['G1', 'G2', 'G3', 'G4'];

        foreach ($courses as $courseIndex => $course) {
            foreach ($groupCodes as $codeIndex => $code) {
                $groups[] = [
                    'course_id' => $course->id,
                    'code' => $course->id . '-' . $code,
                    'name' => $course->name . ' - Grupo ' . $code,
                    'start_date' => Carbon::now()->addDays($codeIndex * 7),
                    'end_date' => Carbon::now()->addDays(120 + ($codeIndex * 7)),
                    'status' => ['draft', 'approved', 'open', 'in_progress'][rand(0, 3)],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        DB::table('groups')->insert($groups);
    }
}