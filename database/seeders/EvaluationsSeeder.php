<?php
// database/seeders/EvaluationsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EvaluationsSeeder extends Seeder
{
    public function run(): void
    {
        $groups = DB::table('groups')->get();
        $teachers = DB::table('users')->where('role', 'like', '%instructor%')->get();

        if ($groups->isEmpty() || $teachers->isEmpty()) {
            $this->command->warn('No hay grupos o instructores disponibles.');
            return;
        }

        $evaluations = [];
        $evaluationTypes = ['Exam', 'Quiz', 'Project', 'Assignment', 'Final'];

        foreach ($groups as $group) {
            foreach ($evaluationTypes as $type) {
                $teacher = $teachers->random();
                
                $evaluations[] = [
                    'group_id' => $group->id,
                    'title' => $type . ' - ' . $group->name,
                    'description' => 'Evaluación de tipo ' . $type . ' para el grupo ' . $group->name,
                    'evaluation_type' => $type,
                    'due_date' => Carbon::now()->addDays(rand(10, 30)),
                    'weight' => rand(5, 25) / 10, // Valores entre 0.5 y 2.5
                    'teacher_creator_id' => $teacher->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        DB::table('evaluations')->insert($evaluations);
        $this->command->info('Evaluaciones creadas: ' . count($evaluations));
    }
}