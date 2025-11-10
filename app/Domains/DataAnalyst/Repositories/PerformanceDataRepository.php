<?php
// app/Domains/DataAnalyst/Repositories/PerformanceDataRepository.php

namespace App\Domains\DataAnalyst\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceDataRepository
{
    /**
     * Obtiene datos de rendimiento del grupo
     */
    public function getGroupPerformanceData(int $groupId, string $period = '30d'): array
    {
        try {
            $dateRange = $this->getDateRange($period);

            return [
                'basic_metrics' => $this->getBasicGroupMetrics($groupId, $dateRange),
                'grade_distribution' => $this->getGradeDistributionData($groupId),
                'instructor_effectiveness' => $this->getInstructorEffectivenessData($groupId),
                'module_performance' => $this->getModulePerformanceData($groupId),
            ];
        } catch (\Exception $e) {
            Log::error("Error getting performance data for group {$groupId}: " . $e->getMessage());
            return [
                'basic_metrics' => [],
                'grade_distribution' => [],
                'instructor_effectiveness' => [],
                'module_performance' => []
            ];
        }
    }

    /**
     * Obtiene métricas básicas del grupo
     */
    private function getBasicGroupMetrics(int $groupId, array $dateRange): array
    {
        $result = DB::table('groups as g')
            ->select([
                'g.id',
                'g.name as group_name',
                DB::raw('COUNT(DISTINCT e.id) as total_students'),
                DB::raw('COUNT(DISTINCT CASE WHEN e.academic_status = "active" THEN e.id END) as active_students'),
                DB::raw('AVG(er.final_grade) as average_grade'),
                DB::raw('AVG(er.attendance_percentage) as average_attendance'),
                // ✅ CORRECCIÓN: Contar estudiantes únicos aprobados
                DB::raw('COUNT(DISTINCT CASE WHEN er.status = "approved" THEN e.id END) as passed_students'),
                DB::raw('COUNT(DISTINCT cs.id) as total_sessions'),
                DB::raw('COUNT(DISTINCT ex.id) as total_exams')
            ])
            ->leftJoin('enrollments as e', 'g.id', '=', 'e.group_id')
            ->leftJoin('enrollment_results as er', 'e.id', '=', 'er.enrollment_id')
            ->leftJoin('class_sessions as cs', 'g.id', '=', 'cs.group_id')
            ->leftJoin('exams as ex', 'g.id', '=', 'ex.group_id')
            ->where('g.id', $groupId)
            ->groupBy('g.id', 'g.name')
            ->first();

        return (array) $result ?? [];
    }

    /**
     * Obtiene distribución de calificaciones
     */
    public function getGradeDistributionData(int $groupId): array
    {
        return DB::table('enrollment_results as er')
            ->select([
                DB::raw('CASE 
                    WHEN er.final_grade >= 18 THEN "A (18-20)"
                    WHEN er.final_grade >= 16 THEN "B (16-17.9)" 
                    WHEN er.final_grade >= 14 THEN "C (14-15.9)"
                    WHEN er.final_grade >= 11 THEN "D (11-13.9)"
                    ELSE "F (0-10.9)"
                END as grade_range'),
                DB::raw('COUNT(*) as student_count'),
                DB::raw(
                    'ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM enrollment_results er2 
                         JOIN enrollments e2 ON er2.enrollment_id = e2.id 
                         WHERE e2.group_id = ? AND er2.final_grade IS NOT NULL)), 2) as percentage'
                )
            ])
            ->addBinding($groupId, 'select')
            ->join('enrollments as e', 'er.enrollment_id', '=', 'e.id')
            ->where('e.group_id', $groupId)
            ->whereNotNull('er.final_grade')
            ->groupBy('grade_range')
            ->orderByRaw('MIN(er.final_grade) DESC')
            ->get()
            ->toArray();
    }

    /**
     * Obtiene efectividad de instructores
     */
    public function getInstructorEffectivenessData(int $groupId): array
    {
        return DB::table('group_teachers as gt')
            ->select([
                'u.id as instructor_id',
                'u.name as instructor_name',
                'u.email as instructor_email',
                DB::raw('COUNT(DISTINCT e.id) as students_taught'),
                DB::raw('ROUND(AVG(g.grade), 2) as average_student_grade'),
                DB::raw('ROUND(AVG(er.final_grade), 2) as average_final_grade'),
                DB::raw('COUNT(DISTINCT sr.id) as rating_count'),
                DB::raw('ROUND(AVG(rd.score), 2) as average_rating')
            ])
            ->join('users as u', 'gt.user_id', '=', 'u.id')
            ->leftJoin('enrollments as e', 'gt.group_id', '=', 'e.group_id')
            ->leftJoin('grades as g', 'e.id', '=', 'g.enrollment_id')
            ->leftJoin('enrollment_results as er', 'e.id', '=', 'er.enrollment_id')
            ->leftJoin('survey_responses as sr', function ($join) {
                $join->on('u.id', '=', 'sr.rateable_id')
                    ->where('sr.rateable_type', '=', 'IncadevUns\\CoreDomain\\Models\\User');
            })
            ->leftJoin('response_details as rd', 'sr.id', '=', 'rd.survey_response_id')
            ->where('gt.group_id', $groupId)
            ->groupBy('u.id', 'u.name', 'u.email')
            ->get()
            ->toArray();
    }

    /**
     * Obtiene desempeño por módulo
     */
    public function getModulePerformanceData(int $groupId): array
    {
        return DB::table('modules as m')
            ->select([
                'm.id',
                'm.title',
                'm.sort as module_order',
                DB::raw('COUNT(DISTINCT e.id) as enrolled_students'),
                DB::raw('COUNT(DISTINCT g.enrollment_id) as evaluated_students'),
                DB::raw('ROUND(AVG(g.grade), 2) as average_grade'),
                DB::raw('ROUND((COUNT(DISTINCT g.enrollment_id) * 100.0 / COUNT(DISTINCT e.id)), 2) as evaluation_rate'),
                DB::raw('COUNT(DISTINCT cs.id) as sessions_held'),
                DB::raw('COUNT(DISTINCT ex.id) as exams_count')
            ])
            ->join('course_versions as cv', 'm.course_version_id', '=', 'cv.id')
            ->join('groups as grp', 'cv.id', '=', 'grp.course_version_id')
            ->leftJoin('enrollments as e', 'grp.id', '=', 'e.group_id')
            ->leftJoin('exams as ex', function ($join) use ($groupId) {
                $join->on('m.id', '=', 'ex.module_id')
                    ->where('ex.group_id', '=', $groupId);
            })
            ->leftJoin('grades as g', function ($join) {
                $join->on('ex.id', '=', 'g.exam_id')
                    ->on('e.id', '=', 'g.enrollment_id');
            })
            ->leftJoin('class_sessions as cs', function ($join) use ($groupId) {
                $join->on('m.id', '=', 'cs.module_id')
                    ->where('cs.group_id', '=', $groupId);
            })
            ->where('grp.id', $groupId)
            ->groupBy('m.id', 'm.title', 'm.sort')
            ->orderBy('m.sort')
            ->get()
            ->toArray();
    }

    /**
     * Obtiene información del grupo
     */
    public function getGroup(int $groupId)
    {
        return DB::table('groups')
            ->where('id', $groupId)
            ->first();
    }

    /**
     * Obtiene promedio institucional para comparación
     */
    public function getInstitutionalAverage(): array
    {
        // Datos simulados - en producción esto vendría de una tabla de benchmarks
        return [
            'average_grade' => 14.5, // Escala 0-20
            'completion_rate' => 75.0,
            'attendance_rate' => 85.0,
            'pass_rate' => 70.0
        ];
    }

    /**
     * Define el rango de fechas
     */
    private function getDateRange(string $period): array
    {
        return match ($period) {
            '7d' => ['start' => now()->subDays(7), 'end' => now()],
            '30d' => ['start' => now()->subDays(30), 'end' => now()],
            '90d' => ['start' => now()->subDays(90), 'end' => now()],
            'all' => ['start' => null, 'end' => null],
            default => ['start' => now()->subDays(30), 'end' => now()],
        };
    }
}
