<?php
// database/seeders/EnrollmentsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnrollmentsSeeder extends Seeder
{
    public function run(): void
    {
        $students = DB::table('students')->get();
        $academicPeriods = DB::table('academic_periods')->get();

        $enrollments = [];
        $enrollmentId = 5000;

        foreach ($students as $index => $student) {
            $enrollments[] = [
                'enrollment_id' => $enrollmentId + $index,
                'student_id' => $student->id,
                'academic_period_id' => $academicPeriods->random()->id,
                'enrollment_type' => 'new',
                'enrollment_date' => Carbon::now()->subDays(rand(1, 30)),
                'status' => 'active',
                'created_at' => Carbon::now(),
            ];
        }

        DB::table('enrollments')->insert($enrollments);
    }
}