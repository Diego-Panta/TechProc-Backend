<?php
// database/seeders/GradeRecordsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GradeRecordsSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener estudiantes y evaluaciones
        $studentUsers = DB::table('users')->where('role', 'like', '%student%')->get();
        $evaluations = DB::table('evaluations')->get();

        if ($studentUsers->isEmpty() || $evaluations->isEmpty()) {
            $this->command->warn('No hay estudiantes o evaluaciones para crear registros de calificaciones.');
            return;
        }

        $gradeRecords = [];

        foreach ($studentUsers as $user) {
            // Tomar algunas evaluaciones aleatorias para este estudiante (no todas)
            $randomEvaluations = $evaluations->random(min(8, $evaluations->count()));
            
            foreach ($randomEvaluations as $evaluation) {
                $gradeRecords[] = [
                    'evaluation_id' => $evaluation->id,
                    'user_id' => $user->id,
                    'obtained_grade' => rand(50, 100) + (rand(0, 99) / 100), // Notas entre 50.00 y 100.99
                    'feedback' => $this->generateFeedback(rand(50, 100)),
                    'record_date' => Carbon::now()->subDays(rand(1, 30)),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        // Eliminar duplicados por si acaso (debido a la constraint UNIQUE)
        $uniqueGradeRecords = collect($gradeRecords)->unique(function ($item) {
            return $item['evaluation_id'] . '-' . $item['user_id'];
        })->values()->all();

        if (!empty($uniqueGradeRecords)) {
            DB::table('grade_records')->insert($uniqueGradeRecords);
            $this->command->info('Registros de calificaciones creados: ' . count($uniqueGradeRecords));
        }
    }

    /**
     * Generar feedback basado en la calificación
     */
    private function generateFeedback($grade): string
    {
        if ($grade >= 90) {
            return 'Excelente trabajo, demuestra dominio completo del tema.';
        } elseif ($grade >= 80) {
            return 'Buen desempeño, comprende bien los conceptos.';
        } elseif ($grade >= 70) {
            return 'Desempeño satisfactorio, puede mejorar en algunos aspectos.';
        } elseif ($grade >= 60) {
            return 'Necesita reforzar algunos conceptos clave.';
        } else {
            return 'Requiere estudio adicional y práctica.';
        }
    }
}