<?php
// database/seeders/CourseInstructorsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CourseInstructorsSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener todos los cursos e instructores
        $courses = DB::table('courses')->get();
        $instructors = DB::table('instructors')->get();

        if ($courses->isEmpty() || $instructors->isEmpty()) {
            $this->command->warn('No hay cursos o instructores disponibles. Ejecuta primero CoursesSeeder e InstructorsSeeder.');
            return;
        }

        $courseInstructors = [];
        $courseInstructorId = 7000;

        // Asignar instructores a cursos
        $assignments = [
            // Curso 1: Desarrollo Web Full Stack - Carlos Rodríguez
            [
                'course_index' => 0,
                'instructor_index' => 0
            ],
            // Curso 2: Data Science con Python - Laura Martínez
            [
                'course_index' => 1,
                'instructor_index' => 1
            ],
            // Curso 3: Introducción a la Programación - Carlos Rodríguez
            [
                'course_index' => 2,
                'instructor_index' => 0
            ],
            // Curso 3 también con Laura Martínez (curso con múltiples instructores)
            [
                'course_index' => 2,
                'instructor_index' => 1
            ]
        ];

        foreach ($assignments as $index => $assignment) {
            $course = $courses[$assignment['course_index']];
            $instructor = $instructors[$assignment['instructor_index']];

            $courseInstructors[] = [
                'id_course_inst' => $courseInstructorId + $index,
                'instructor_id' => $instructor->id,
                'course_id' => $course->id,
                'assigned_date' => Carbon::now()->subDays(rand(1, 30)),
            ];
        }

        DB::table('course_instructors')->insert($courseInstructors);
        $this->command->info('Asignaciones curso-instructor creadas: ' . count($courseInstructors));
    }
}