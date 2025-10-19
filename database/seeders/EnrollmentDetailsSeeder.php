<?php
// database/seeders/EnrollmentDetailsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnrollmentDetailsSeeder extends Seeder
{
    public function run(): void
    {
        $enrollments = DB::table('enrollments')->get();
        $subjects = DB::table('subjects')->get();
        $courseOfferings = DB::table('course_offerings')->get();

        $enrollmentDetails = [];

        foreach ($enrollments as $enrollment) {
            $enrollmentDetails[] = [
                'enrollment_id' => $enrollment->id,
                'subject_id' => $subjects->random()->id,
                'course_offering_id' => $courseOfferings->random()->id,
                'status' => 'active',
                'created_at' => Carbon::now(),
            ];
        }

        DB::table('enrollment_details')->insert($enrollmentDetails);
    }
}