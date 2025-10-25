<?php
// database/seeders/FinalGradesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinalGradesSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener estudiantes y grupos
        $studentUsers = DB::table('users')->where('role', 'like', '%student%')->get();
        $groups = DB::table('groups')->get();

        if ($studentUsers->isEmpty() || $groups->isEmpty()) {
            $this->command->warn('No hay estudiantes o grupos para crear calificaciones finales.');
            return;
        }

        $finalGrades = [];

        foreach ($studentUsers as $user) {
            // Tomar algunos grupos aleatorios para este estudiante (no todos)
            $randomGroups = $groups->random(min(3, $groups->count()));
            
            foreach ($randomGroups as $group) {
                $finalGrade = rand(50, 100) + (rand(0, 99) / 100); // Nota entre 50.00 y 100.99
                $programStatus = $finalGrade >= 70 ? 'Passed' : 'Failed'; // Usando 70 como nota de aprobación por defecto

                $finalGrades[] = [
                    'user_id' => $user->id,
                    'group_id' => $group->id,
                    'final_grade' => $finalGrade,
                    'program_status' => $programStatus,
                    'calculation_date' => Carbon::now()->subDays(rand(1, 60)),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        // Eliminar duplicados por si acaso (debido a la constraint UNIQUE)
        $uniqueFinalGrades = collect($finalGrades)->unique(function ($item) {
            return $item['user_id'] . '-' . $item['group_id'];
        })->values()->all();

        if (!empty($uniqueFinalGrades)) {
            DB::table('final_grades')->insert($uniqueFinalGrades);
            $this->command->info('Calificaciones finales creadas: ' . count($uniqueFinalGrades));
            $this->command->info(' - Aprobados: ' . collect($uniqueFinalGrades)->where('program_status', 'Passed')->count());
            $this->command->info(' - Reprobados: ' . collect($uniqueFinalGrades)->where('program_status', 'Failed')->count());
        }
    }
}