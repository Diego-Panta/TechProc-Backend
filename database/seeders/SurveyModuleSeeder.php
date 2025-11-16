<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use IncadevUns\CoreDomain\Models\Survey;
use IncadevUns\CoreDomain\Models\SurveyQuestion;
use IncadevUns\CoreDomain\Models\SurveyResponse;
use IncadevUns\CoreDomain\Models\ResponseDetail;
use App\Domains\AuthenticationSessions\Models\User;

class SurveyModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Generando datos de muestra para encuestas...');

        // Obtener usuarios con rol student
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'student');
        })->limit(3)->get();

        // Si no hay estudiantes, usar cualquier usuario disponible
        if ($students->isEmpty()) {
            $this->command->warn('⚠ No se encontraron usuarios con rol "student", usando cualquier usuario activo...');
            $students = User::where('email', '!=', 'admin@incadev.com')->limit(3)->get();
            
            if ($students->isEmpty()) {
                $this->command->error('✗ No se encontraron usuarios disponibles para crear encuestas');
                $this->command->warn('Por favor, ejecuta primero el seeder de usuarios:');
                $this->command->info('  php artisan db:seed --class="IncadevUns\\CoreDomain\\Database\\Seeders\\UserSeeder"');
                return;
            }
        }

        $this->command->info("✓ Encontrados {$students->count()} usuarios para las encuestas");

        /* ======================================================
           1. ENCUESTA
        ====================================================== */
        $survey = Survey::create([
            'title'       => 'Encuesta de Satisfacción Estudiantil 2025',
            'description' => 'Evaluación institucional de satisfacción.',
        ]);

        /* ======================================================
           2. PREGUNTAS (3 preguntas EXACTAS)
        ====================================================== */
        $q1 = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question'  => '¿Qué tan satisfecho estás con la calidad de enseñanza?',
            'order'     => 1,
        ]);

        $q2 = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question'  => '¿Cómo calificas la infraestructura de la institución?',
            'order'     => 2,
        ]);

        $q3 = SurveyQuestion::create([
            'survey_id' => $survey->id,
            'question'  => '¿El personal administrativo resolvió adecuadamente tus dudas?',
            'order'     => 3,
        ]);

        /* ======================================================
           3. RESPUESTAS (usando usuarios reales)
        ====================================================== */
        $responses = [];

        foreach ($students as $index => $student) {
            $responses[] = SurveyResponse::create([
                'survey_id'     => $survey->id,
                'user_id'       => $student->id,
                'rateable_id'   => $survey->id,
                'rateable_type' => Survey::class,
                'date'          => now()->subDays($index * 2)->format('Y-m-d'),
            ]);
        }

        /* ======================================================
           4. DETALLES (score EXACTOS, definidos por ti)
        ====================================================== */

        // --- RESPUESTA 1 (Primer estudiante) ---
        ResponseDetail::create([
            'survey_response_id' => $responses[0]->id,
            'survey_question_id' => $q1->id,
            'score'              => 4,
        ]);
        ResponseDetail::create([
            'survey_response_id' => $responses[0]->id,
            'survey_question_id' => $q2->id,
            'score'              => 3,
        ]);
        ResponseDetail::create([
            'survey_response_id' => $responses[0]->id,
            'survey_question_id' => $q3->id,
            'score'              => 5,
        ]);

        // --- RESPUESTA 2 (Segundo estudiante) ---
        ResponseDetail::create([
            'survey_response_id' => $responses[1]->id,
            'survey_question_id' => $q1->id,
            'score'              => 5,
        ]);
        ResponseDetail::create([
            'survey_response_id' => $responses[1]->id,
            'survey_question_id' => $q2->id,
            'score'              => 4,
        ]);
        ResponseDetail::create([
            'survey_response_id' => $responses[1]->id,
            'survey_question_id' => $q3->id,
            'score'              => 4,
        ]);

        // --- RESPUESTA 3 (Tercer estudiante) ---
        if (isset($responses[2])) {
            ResponseDetail::create([
                'survey_response_id' => $responses[2]->id,
                'survey_question_id' => $q1->id,
                'score'              => 3,
            ]);
            ResponseDetail::create([
                'survey_response_id' => $responses[2]->id,
                'survey_question_id' => $q2->id,
                'score'              => 2,
            ]);
            ResponseDetail::create([
                'survey_response_id' => $responses[2]->id,
                'survey_question_id' => $q3->id,
                'score'              => 3,
            ]);
        }

        $this->command->info("✓ Encuesta creada: {$survey->title}");
        $this->command->info("✓ {$students->count()} respuestas de encuesta creadas");
        $this->command->info("✓ 9 detalles de respuesta creados");
        $this->command->info('✔ Datos de encuesta cargados exitosamente.');
    }
}