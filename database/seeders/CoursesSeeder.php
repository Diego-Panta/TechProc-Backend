<?php
// database/seeders/CoursesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CoursesSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            [
                'course_id' => 1001,
                'title' => 'Desarrollo Web Full Stack',
                'name' => 'Full Stack Developer',
                'description' => 'Curso completo de desarrollo web frontend y backend',
                'level' => 'intermediate',
                'duration' => 120.50,
                'sessions' => 40,
                'selling_price' => 1500.00,
                'discount_price' => 1200.00,
                'prerequisites' => 'Conocimientos básicos de programación',
                'certificate_name' => true,
                'certificate_issuer' => 'Academia Tech',
                'bestseller' => true,
                'featured' => true,
                'highest_rated' => true,
                'status' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'course_id' => 1002,
                'title' => 'Data Science con Python',
                'name' => 'Data Science Professional',
                'description' => 'Aprende análisis de datos y machine learning con Python',
                'level' => 'advanced',
                'duration' => 180.25,
                'sessions' => 60,
                'selling_price' => 2000.00,
                'discount_price' => 1600.00,
                'prerequisites' => 'Conocimientos de Python y estadística',
                'certificate_name' => true,
                'certificate_issuer' => 'Academia Tech',
                'bestseller' => true,
                'featured' => false,
                'highest_rated' => true,
                'status' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'course_id' => 1003,
                'title' => 'Introducción a la Programación',
                'name' => 'Programming Fundamentals',
                'description' => 'Fundamentos de programación para principiantes',
                'level' => 'basic',
                'duration' => 80.00,
                'sessions' => 30,
                'selling_price' => 800.00,
                'discount_price' => 600.00,
                'prerequisites' => 'Ninguno',
                'certificate_name' => true,
                'certificate_issuer' => 'Academia Tech',
                'bestseller' => false,
                'featured' => true,
                'highest_rated' => false,
                'status' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        DB::table('courses')->insert($courses);
    }
}