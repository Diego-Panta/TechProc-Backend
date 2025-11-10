<?php
// app/Domains/DataAnalyst/Repositories/StudentDataRepository.php

namespace App\Domains\DataAnalyst\Repositories;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentDataRepository
{
    /**
     * Obtiene todos los indicadores para un estudiante específico
     */
    public function getStudentIndicators(int $enrollmentId): array
    {
        $enrollment = $this->getEnrollment($enrollmentId);
        if (!$enrollment) {
            return [];
        }

        $data = DB::table('enrollments as e')
            ->select([
                'e.id',
                'e.group_id',
                'e.user_id',
                'e.payment_status',
                'e.academic_status',
                'er.final_grade',
                'er.attendance_percentage',
                'er.status as enrollment_result_status',

                // Tickets de soporte
                DB::raw('(SELECT COUNT(*) FROM tickets t WHERE t.user_id = e.user_id AND t.created_at >= NOW() - INTERVAL 30 DAY) as support_tickets'),

                // Citas de orientación
                DB::raw('(SELECT COUNT(*) FROM appointments a WHERE a.student_id = e.user_id AND a.status = "completed") as counseling_sessions'),

                // Satisfacción en encuestas
                DB::raw('(SELECT AVG(rd.score) FROM survey_responses sr 
                         JOIN response_details rd ON sr.id = rd.survey_response_id 
                         WHERE sr.user_id = e.user_id AND sr.created_at >= NOW() - INTERVAL 60 DAY) as satisfaction_score'),

                // Ausencias recientes (últimos 7 días)
                DB::raw('(SELECT COUNT(*) FROM attendances a 
                         JOIN class_sessions cs ON a.class_session_id = cs.id 
                         WHERE a.enrollment_id = e.id AND a.status = "absent" 
                         AND cs.start_time >= NOW() - INTERVAL 7 DAY) as recent_absences'),

                // Ausencias consecutivas
                DB::raw('(SELECT MAX(consecutive_count) FROM (
                         SELECT COUNT(*) as consecutive_count FROM attendances a 
                         JOIN class_sessions cs ON a.class_session_id = cs.id 
                         WHERE a.enrollment_id = e.id AND a.status = "absent" 
                         AND cs.start_time >= NOW() - INTERVAL 14 DAY
                         GROUP BY DATE(cs.start_time)
                     ) as absences) as consecutive_absences'),

                // Última actividad
                DB::raw('(SELECT MAX(cs.start_time) FROM attendances a 
                         JOIN class_sessions cs ON a.class_session_id = cs.id 
                         WHERE a.enrollment_id = e.id AND a.status = "present") as last_activity'),

                // Información de exámenes
                DB::raw('(SELECT COUNT(*) FROM grades g WHERE g.enrollment_id = e.id) as exams_taken'),
                DB::raw('(SELECT COUNT(*) FROM grades g WHERE g.enrollment_id = e.id AND g.grade < 11) as failed_exams'),

                // Tendencia de notas (simplificada)
                DB::raw('(SELECT AVG(grade) FROM grades g WHERE g.enrollment_id = e.id) as average_grade'),

                // Retrasos en pagos
                DB::raw('(SELECT COUNT(*) FROM enrollment_payments ep 
                         WHERE ep.enrollment_id = e.id AND ep.status = "overdue") as payment_delays'),
            ])
            ->leftJoin('enrollment_results as er', 'e.id', '=', 'er.enrollment_id')
            ->where('e.id', $enrollmentId)
            ->first();

        if (!$data) {
            return [];
        }

        // Calcular tendencia de notas (MEJORADO)
        $gradeTrend = $this->calculateGradeTrend($enrollmentId);

        return array_merge((array) $data, [
            'grade_trend' => $gradeTrend,
            'forum_participation' => $this->getForumParticipation($data->user_id ?? 0),
            'peer_interactions' => $this->getPeerInteractions($enrollmentId),
            'class_participation' => $this->getClassParticipation($enrollmentId),
            'total_classes' => $this->getTotalClasses($data->group_id ?? 0),
            'has_academic_data' => $this->hasAcademicData($enrollmentId),
            'has_class_activities' => $this->hasClassActivities($data->group_id ?? 0),
        ]);
    }

    /**
     * NUEVO: Calcula la tendencia de notas con regresión lineal
     */
    private function calculateGradeTrend(int $enrollmentId): float
    {
        $grades = DB::table('grades')
            ->where('enrollment_id', $enrollmentId)
            ->orderBy('created_at')
            ->pluck('grade')
            ->toArray();

        if (count($grades) < 2) {
            return 0.0; // No hay suficiente data para tendencia
        }

        // Regresión lineal simple para tendencia real
        $n = count($grades);
        $sumX = $sumY = $sumXY = $sumX2 = 0;
        
        foreach ($grades as $i => $grade) {
            $sumX += $i;
            $sumY += $grade;
            $sumXY += $i * $grade;
            $sumX2 += $i * $i;
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        return round($slope, 2);
    }

    /**
     * NUEVO: Obtiene el total de clases en el grupo
     */
    public function getTotalClasses(int $groupId): int
    {
        return DB::table('class_sessions')
            ->where('group_id', $groupId)
            ->count();
    }

    /**
     * NUEVO: Verifica si hay datos académicos
     */
    public function hasAcademicData(int $enrollmentId): bool
    {
        $examsCount = DB::table('grades')
            ->where('enrollment_id', $enrollmentId)
            ->count();
            
        $hasFinalGrade = DB::table('enrollment_results')
            ->where('enrollment_id', $enrollmentId)
            ->whereNotNull('final_grade')
            ->exists();

        return $examsCount > 0 || $hasFinalGrade;
    }

    /**
     * NUEVO: Verifica si el grupo tiene actividades
     */
    public function hasClassActivities(int $groupId): bool
    {
        $classCount = DB::table('class_sessions')
            ->where('group_id', $groupId)
            ->where('start_time', '<=', now())
            ->count();

        return $classCount > 0;
    }

    // Métodos existentes (mantener igual)
    private function getForumParticipation(int $userId): int
    {
        return DB::table('comments')
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
    }

    private function getPeerInteractions(int $enrollmentId): int
    {
        // Placeholder - implementar lógica específica
        return rand(0, 10);
    }

    private function getClassParticipation(int $enrollmentId): int
    {
        // Placeholder - implementar lógica específica  
        return rand(0, 5);
    }

    public function getDataCompleteness(int $enrollmentId): float
    {
        try {
            $enrollment = $this->getEnrollment($enrollmentId);
            if (!$enrollment) {
                return 0.0;
            }

            $totalPossibleDataPoints = 8;
            $availableDataPoints = 0;

            $indicators = $this->getStudentIndicators($enrollmentId);

            // Verificar disponibilidad de datos clave
            if (isset($indicators['final_grade']) && $indicators['final_grade'] > 0) {
                $availableDataPoints++;
            }
            if (isset($indicators['exams_taken']) && $indicators['exams_taken'] > 0) {
                $availableDataPoints++;
            }
            if (isset($indicators['attendance_percentage']) && $indicators['attendance_percentage'] >= 0) {
                $availableDataPoints++;
            }
            if (isset($indicators['recent_absences']) && $indicators['recent_absences'] >= 0) {
                $availableDataPoints++;
            }
            if (isset($indicators['payment_status'])) {
                $availableDataPoints++;
            }
            if (isset($indicators['support_tickets']) && $indicators['support_tickets'] >= 0) {
                $availableDataPoints++;
            }
            if (isset($indicators['counseling_sessions']) && $indicators['counseling_sessions'] >= 0) {
                $availableDataPoints++;
            }
            if (isset($indicators['satisfaction_score']) && $indicators['satisfaction_score'] > 0) {
                $availableDataPoints++;
            }

            $completeness = ($availableDataPoints / $totalPossibleDataPoints) * 100;

            return min($completeness, 100.0);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    public function getEnrollment(int $enrollmentId)
    {
        return DB::table('enrollments')
            ->where('id', $enrollmentId)
            ->where('academic_status', 'active')
            ->first();
    }
}