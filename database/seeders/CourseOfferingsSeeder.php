<?php
// database/seeders/CourseOfferingsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CourseOfferingsSeeder extends Seeder
{
    public function run(): void
    {
        $courses = DB::table('courses')->get();
        $academicPeriods = DB::table('academic_periods')->get();
        $instructors = DB::table('instructors')->get();

        // Verificar que hay instructores disponibles
        if ($instructors->isEmpty()) {
            $this->command->warn('No hay instructores disponibles. Creando instructor temporal...');
            
            // Crear un instructor temporal si no hay ninguno
            $user = DB::table('users')->where('role', 'like', '%instructor%')->first();
            if (!$user) {
                $userId = DB::table('users')->insertGetId([
                    'first_name' => 'Instructor',
                    'last_name' => 'Temporal',
                    'full_name' => 'Instructor Temporal',
                    'dni' => '99999999',
                    'document' => '99999999',
                    'email' => 'instructor.temporal@email.com',
                    'phone_number' => '+51999999999',
                    'password' => bcrypt('password123'),
                    'gender' => 'male',
                    'country' => 'Perú',
                    'role' => json_encode(['instructor']),
                    'status' => 'active',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            } else {
                $userId = $user->id;
            }

            DB::table('instructors')->insert([
                'instructor_id' => 9999,
                'user_id' => $userId,
                'bio' => 'Instructor temporal para seeders',
                'expertise_area' => 'Desarrollo General',
                'status' => 'active',
                'created_at' => Carbon::now(),
            ]);

            $instructors = DB::table('instructors')->get();
        }

        $courseOfferings = [];
        $offeringId = 8000;

        foreach ($courses as $index => $course) {
            $courseOfferings[] = [
                'course_offering_id' => $offeringId + $index,
                'course_id' => $course->id,
                'academic_period_id' => $academicPeriods->random()->id,
                'instructor_id' => $instructors->random()->id,
                'schedule' => 'Lunes y Miércoles 18:00-20:00',
                'delivery_method' => 'virtual',
                'created_at' => Carbon::now(),
            ];
        }

        DB::table('course_offerings')->insert($courseOfferings);
        
        $this->command->info('Course offerings creados: ' . count($courseOfferings));
    }
}