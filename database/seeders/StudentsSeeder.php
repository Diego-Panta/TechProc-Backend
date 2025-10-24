<?php
// database/seeders/StudentsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentsSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->where('role', 'like', '%student%')->get();
        $companies = DB::table('companies')->get();

        $students = [];
        $studentId = 1000;

        foreach ($users as $index => $user) {
            $students[] = [
                'student_id' => $studentId + $index,
                'user_id' => $user->id,
                'company_id' => $companies->random()->id,
                'document_number' => $user->dni,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone_number,
                'status' => 'active',
                'created_at' => Carbon::now(),
            ];
        }

        DB::table('students')->insert($students);
    }
}