<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use IncadevUns\CoreDomain\Enums\PaymentVerificationStatus;
use IncadevUns\CoreDomain\Enums\TicketStatus;
use IncadevUns\CoreDomain\Enums\TicketPriority;
use IncadevUns\CoreDomain\Enums\AppointmentStatus;
use IncadevUns\CoreDomain\Enums\TicketType;

class CompleteSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Obtener usuarios existentes
            $userModelClass = config('auth.providers.users.model', 'App\Models\User');
            
            $users = $userModelClass::all()->keyBy('dni');
            $teachers = $userModelClass::role('teacher')->get();
            $students = $userModelClass::role('student')->get();

            // Obtener grupos y matrículas existentes
            $groups = \IncadevUns\CoreDomain\Models\Group::all();
            $enrollments = \IncadevUns\CoreDomain\Models\Enrollment::all();

            // --- ENROLLMENT PAYMENTS (Pagos adicionales) ---
            $this->createEnrollmentPayments($enrollments);

            // --- SURVEYS & QUESTIONS (Encuestas y preguntas) ---
            $surveys = $this->createSurveys();
            $surveyQuestions = $this->createSurveyQuestions($surveys);

            // --- SURVEY RESPONSES & DETAILS (Respuestas a encuestas) ---
            $this->createSurveyResponses($surveys, $surveyQuestions, $users, $students, $teachers);

            // --- TICKETS (Tickets de soporte) ---
            $this->createTickets($users, $students);

            // --- APPOINTMENTS (Citas entre profesores y estudiantes) ---
            $this->createAppointments($teachers, $students);
        });
    }

    private function createEnrollmentPayments($enrollments)
    {
        $paymentStatuses = PaymentVerificationStatus::cases();
        
        foreach ($enrollments as $enrollment) {
            // Crear 1-2 pagos adicionales por matrícula
            $numPayments = rand(1, 2);
            
            for ($i = 0; $i < $numPayments; $i++) {
                $isMainPayment = $i === 0;
                $status = $isMainPayment ? 
                    PaymentVerificationStatus::Approved : 
                    $paymentStatuses[array_rand($paymentStatuses)];
                
                \IncadevUns\CoreDomain\Models\EnrollmentPayment::create([
                    'enrollment_id' => $enrollment->id,
                    'operation_number' => 'OP-' . Carbon::now()->format('Ymd') . '-' . $enrollment->id . '-' . ($i + 1),
                    'agency_number' => 'AG-' . str_pad(rand(1, 10), 3, '0', STR_PAD_LEFT),
                    'operation_date' => Carbon::now()->subDays(rand(1, 30)),
                    'amount' => $isMainPayment ? $enrollment->group->courseVersion->price : rand(50, 150),
                    'evidence_path' => 'payments/receipt_' . $enrollment->id . '_' . ($i + 1) . '.jpg',
                    'status' => $status->value,
                    'created_at' => Carbon::now()->subDays(rand(1, 60)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 30)),
                ]);
            }
        }

        $this->command->info('Enrollment payments created successfully.');
    }

    private function createSurveys()
    {
        $surveys = [
            [
                'title' => 'Satisfacción General del Curso',
                'description' => 'Encuesta para medir la satisfacción general con el curso y la metodología de enseñanza.',
            ],
            [
                'title' => 'Evaluación del Profesor',
                'description' => 'Encuesta para evaluar el desempeño del profesor en el curso.',
            ],
            [
                'title' => 'Satisfacción con la Plataforma',
                'description' => 'Encuesta para conocer la experiencia de uso de la plataforma educativa.',
            ]
        ];

        $createdSurveys = [];
        foreach ($surveys as $survey) {
            $createdSurveys[] = \IncadevUns\CoreDomain\Models\Survey::create($survey);
        }

        $this->command->info('Surveys created successfully.');
        return $createdSurveys;
    }

    private function createSurveyQuestions($surveys)
    {
        $questions = [];
        
        // Preguntas para la encuesta de satisfacción del curso
        $courseQuestions = [
            ['question' => '¿Cómo calificarías el contenido del curso?', 'order' => 1],
            ['question' => '¿El material proporcionado fue adecuado y útil?', 'order' => 2],
            ['question' => '¿La duración del curso fue la adecuada?', 'order' => 3],
            ['question' => '¿Recomendarías este curso a otros compañeros?', 'order' => 4],
        ];

        // Preguntas para la evaluación del profesor
        $teacherQuestions = [
            ['question' => '¿El profesor demostró dominio del tema?', 'order' => 1],
            ['question' => '¿La metodología de enseñanza fue efectiva?', 'order' => 2],
            ['question' => '¿El profesor resolvió tus dudas adecuadamente?', 'order' => 3],
            ['question' => '¿La comunicación del profesor fue clara?', 'order' => 4],
        ];

        // Preguntas para la plataforma
        $platformQuestions = [
            ['question' => '¿La plataforma es fácil de usar?', 'order' => 1],
            ['question' => '¿La velocidad de la plataforma es adecuada?', 'order' => 2],
            ['question' => '¿Encontraste toda la información que necesitabas?', 'order' => 3],
        ];

        $allQuestions = [];
        
        // Crear preguntas para cada encuesta
        foreach ($courseQuestions as $q) {
            $question = \IncadevUns\CoreDomain\Models\SurveyQuestion::create([
                'survey_id' => $surveys[0]->id,
                'question' => $q['question'],
                'order' => $q['order'],
            ]);
            $allQuestions[] = $question;
        }

        foreach ($teacherQuestions as $q) {
            $question = \IncadevUns\CoreDomain\Models\SurveyQuestion::create([
                'survey_id' => $surveys[1]->id,
                'question' => $q['question'],
                'order' => $q['order'],
            ]);
            $allQuestions[] = $question;
        }

        foreach ($platformQuestions as $q) {
            $question = \IncadevUns\CoreDomain\Models\SurveyQuestion::create([
                'survey_id' => $surveys[2]->id,
                'question' => $q['question'],
                'order' => $q['order'],
            ]);
            $allQuestions[] = $question;
        }

        $this->command->info('Survey questions created successfully.');
        return $allQuestions;
    }

    private function createSurveyResponses($surveys, $surveyQuestions, $users, $students, $teachers)
    {
        $rateableModels = [
            \IncadevUns\CoreDomain\Models\Group::class,
            \IncadevUns\CoreDomain\Models\Course::class,
            \IncadevUns\CoreDomain\Models\TeacherProfile::class,
        ];

        $groups = \IncadevUns\CoreDomain\Models\Group::all();
        $courses = \IncadevUns\CoreDomain\Models\Course::all();

        foreach ($surveys as $surveyIndex => $survey) {
            // Para cada encuesta, crear respuestas de varios usuarios
            $respondents = $students->random(min(8, $students->count()));
            
            foreach ($respondents as $user) {
                $rateable = null;
                
                // Asignar rateable basado en el índice de la encuesta
                if ($surveyIndex === 0) {
                    // Encuesta de curso - usar grupo
                    $rateable = $groups->random();
                } elseif ($surveyIndex === 1) {
                    // Encuesta de profesor - usar perfil de profesor
                    $teacherProfile = $teachers->random()->teacherProfile;
                    if ($teacherProfile) {
                        $rateable = $teacherProfile;
                    }
                } elseif ($surveyIndex === 2) {
                    // Encuesta de plataforma - usar curso
                    $rateable = $courses->random();
                }

                if ($rateable) {
                    $surveyResponse = \IncadevUns\CoreDomain\Models\SurveyResponse::create([
                        'survey_id' => $survey->id,
                        'user_id' => $user->id,
                        'rateable_type' => get_class($rateable),
                        'rateable_id' => $rateable->id,
                        'date' => Carbon::now()->subDays(rand(1, 20)),
                    ]);

                    // Crear detalles de respuesta para cada pregunta de esta encuesta
                    $surveyQuestionsForThisSurvey = collect($surveyQuestions)->filter(function($question) use ($survey) {
                        return $question->survey_id === $survey->id;
                    });
                    
                    foreach ($surveyQuestionsForThisSurvey as $question) {
                        \IncadevUns\CoreDomain\Models\ResponseDetail::create([
                            'survey_response_id' => $surveyResponse->id,
                            'survey_question_id' => $question->id,
                            'score' => rand(3, 5), // Puntuación entre 3-5 (escala 1-5)
                        ]);
                    }
                }
            }
        }

        $this->command->info('Survey responses and details created successfully.');
    }

    private function createTickets($users, $students)
    {
        $ticketTypes = TicketType::cases();
        $statuses = TicketStatus::cases();
        $priorities = TicketPriority::cases();

        $ticketTitles = [
            'Problema para acceder a las clases grabadas',
            'Error en la plataforma de pagos',
            'Duda sobre el material del curso',
            'Problema con la descarga de certificados',
            'Consulta sobre el próximo módulo',
            'Error en la calificación del examen',
            'Problema de audio en videollamadas',
            'Solicitud de reembolso',
            'Consulta sobre horarios de tutoría',
            'Reporte de bug en la plataforma',
        ];

        // Mapear títulos a tipos apropiados
        $titleToTypeMap = [
            'Problema para acceder a las clases grabadas' => TicketType::Technical,
            'Error en la plataforma de pagos' => TicketType::Technical,
            'Duda sobre el material del curso' => TicketType::Academic,
            'Problema con la descarga de certificados' => TicketType::Administrative,
            'Consulta sobre el próximo módulo' => TicketType::Academic,
            'Error en la calificación del examen' => TicketType::Academic,
            'Problema de audio en videollamadas' => TicketType::Technical,
            'Solicitud de reembolso' => TicketType::Administrative,
            'Consulta sobre horarios de tutoría' => TicketType::Inquiry,
            'Reporte de bug en la plataforma' => TicketType::Technical,
        ];

        foreach (range(1, 15) as $i) {
            $user = $users->random();
            $title = $ticketTitles[array_rand($ticketTitles)];
            $type = $titleToTypeMap[$title] ?? $ticketTypes[array_rand($ticketTypes)];
            
            \IncadevUns\CoreDomain\Models\Ticket::create([
                'user_id' => $user->id,
                'title' => $title,
                'type' => $type->value,
                'status' => $statuses[array_rand($statuses)]->value,
                'priority' => $priorities[array_rand($priorities)]->value,
                'created_at' => Carbon::now()->subDays(rand(1, 60)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),
            ]);
        }

        $this->command->info('Tickets created successfully.');
    }

    private function createAppointments($teachers, $students)
    {
        $statuses = AppointmentStatus::cases();

        foreach (range(1, 10) as $i) {
            $teacher = $teachers->random();
            $student = $students->random();
            
            $startTime = Carbon::now()->addDays(rand(1, 30))->setHour(rand(9, 17))->setMinute(0)->setSecond(0);
            $endTime = (clone $startTime)->addHours(1);

            \IncadevUns\CoreDomain\Models\Appointment::create([
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $statuses[array_rand($statuses)]->value,
                'meet_url' => 'https://meet.google.com/' . substr(md5($teacher->id . $student->id . $i), 0, 10),
                'created_at' => Carbon::now()->subDays(rand(1, 15)),
                'updated_at' => Carbon::now()->subDays(rand(0, 10)),
            ]);
        }

        $this->command->info('Appointments created successfully.');
    }
}