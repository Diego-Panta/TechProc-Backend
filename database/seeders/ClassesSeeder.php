<?php
// database/seeders/ClassesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClassesSeeder extends Seeder
{
    public function run(): void
    {
        $groups = DB::table('groups')->get();

        $classes = [];

        foreach ($groups as $group) {
            for ($i = 1; $i <= 10; $i++) {
                $classDate = Carbon::now()->addDays($i * 2);
                
                $classes[] = [
                    'group_id' => $group->id,
                    'class_name' => 'Clase ' . $i . ' - ' . $group->name,
                    'class_date' => $classDate->format('Y-m-d'),
                    'start_time' => '18:00:00',
                    'end_time' => '20:00:00',
                    'platform' => 'Zoom',
                    'meeting_url' => 'https://zoom.us/j/' . rand(100000000, 999999999),
                    'external_meeting_id' => 'zoom-' . rand(100000000, 999999999),
                    'meeting_password' => 'pass' . rand(1000, 9999),
                    'allow_recording' => true,
                    'max_participants' => 100,
                    'class_status' => 'SCHEDULED',
                    'created_at' => Carbon::now(),
                ];
            }
        }

        DB::table('classes')->insert($classes);
    }
}