<?php
// app/Domains/DataAnalyst/Services/Charts/PerformanceChartService.php

namespace App\Domains\DataAnalyst\Services\Charts;

use App\Domains\DataAnalyst\Services\Traits\QueryBuilderTrait;
use App\Domains\DataAnalyst\Services\Traits\CacheManagerTrait;
use App\Domains\DataAnalyst\Services\Traits\DataFormatterTrait;
use Illuminate\Support\Facades\Log;

class PerformanceChartService
{
    use QueryBuilderTrait, CacheManagerTrait, DataFormatterTrait;

    protected $bigQuery;

    public function __construct($bigQuery)
    {
        $this->bigQuery = $bigQuery;
    }

    /**
     * Distribución de Calificaciones (Histograma)
     */
    public function getGradeDistribution(array $filters = []): array
    {
        $whereConditions = $this->buildGradeDistributionWhereClause($filters);

        $query = "
            SELECT 
                CASE 
                    WHEN gr.grade BETWEEN 0 AND 5 THEN '0-5'
                    WHEN gr.grade BETWEEN 6 AND 10 THEN '6-10' 
                    WHEN gr.grade BETWEEN 11 AND 13 THEN '11-13'
                    WHEN gr.grade BETWEEN 14 AND 16 THEN '14-16'
                    WHEN gr.grade BETWEEN 17 AND 20 THEN '17-20'
                    ELSE 'Sin calificación'
                END as grade_range,
                CASE 
                    WHEN gr.grade BETWEEN 0 AND 10 THEN 'Reprobado'
                    WHEN gr.grade BETWEEN 11 AND 20 THEN 'Aprobado'
                    ELSE 'Sin calificación'
                END as status,
                COUNT(DISTINCT e.id) as student_count
            FROM `lms_analytics.grades` gr
            JOIN `lms_analytics.enrollments` e ON gr.enrollment_id = e.id
            JOIN `lms_analytics.groups` g ON e.group_id = g.id
            LEFT JOIN `lms_analytics.exams` ex ON gr.exam_id = ex.id
            WHERE gr.grade IS NOT NULL 
              AND g.status = 'active'
              AND e.academic_status = 'active'
              AND e.payment_status = 'paid'
              {$whereConditions}
            GROUP BY grade_range, status
            ORDER BY 
                CASE grade_range
                    WHEN '0-5' THEN 1
                    WHEN '6-10' THEN 2
                    WHEN '11-13' THEN 3
                    WHEN '14-16' THEN 4
                    WHEN '17-20' THEN 5
                    ELSE 6
                END
        ";

        $cacheKey = $this->generateCacheKey('grade_distribution', $filters);

        try {
            $results = $this->executeCachedQuery($query, $cacheKey);
            $stats = $this->calculateGradeStats($filters);

            return [
                'grade_distribution' => $results,
                'statistics' => $stats,
                'filters_applied' => $this->getChartFiltersSummary($filters)
            ];
        } catch (\Exception $e) {
            Log::error('Error en getGradeDistribution: ' . $e->getMessage());
            return [
                'grade_distribution' => [],
                'statistics' => $this->getDefaultGradeStats(),
                'filters_applied' => []
            ];
        }
    }

    /**
     * WHERE clause MEJORADO para distribución de calificaciones
     */
    private function buildGradeDistributionWhereClause(array $filters): string
    {
        $conditions = [];

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'group_id':
                    $conditions[] = "g.id = " . (int)$value;
                    break;
                case 'course_version_id':
                    $conditions[] = "g.course_version_id = " . (int)$value;
                    break;
                case 'start_date':
                    $conditions[] = "ex.start_time >= TIMESTAMP('{$value}')";
                    break;
                case 'end_date':
                    $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                    $conditions[] = "ex.start_time < TIMESTAMP('{$nextDay}')";
                    break;
                case 'min_grade':
                    $conditions[] = "gr.grade >= " . (float)$value;
                    break;
                case 'max_grade':
                    $conditions[] = "gr.grade <= " . (float)$value;
                    break;
            }
        }

        return $conditions ? ' AND ' . implode(' AND ', $conditions) : '';
    }

    /**
     * Correlación Asistencia vs Calificación
     */
    public function getAttendanceGradeCorrelation(array $filters = []): array
    {
        $whereConditions = $this->buildAttendanceGradeCorrelationWhereClause($filters);

        $query = "
            WITH student_attendance AS (
                SELECT 
                    e.id as enrollment_id,
                    e.user_id,
                    u.name as student_name,
                    g.name as group_name,
                    COUNT(DISTINCT cs.id) as total_sessions,
                    COUNT(DISTINCT CASE WHEN a.status = 'present' THEN cs.id END) as present_count,
                    CASE 
                        WHEN COUNT(DISTINCT cs.id) > 0 THEN 
                            ROUND(COUNT(DISTINCT CASE WHEN a.status = 'present' THEN cs.id END) * 100.0 / COUNT(DISTINCT cs.id), 2)
                        ELSE 0 
                    END as attendance_rate
                FROM `lms_analytics.enrollments` e
                JOIN `lms_analytics.users` u ON e.user_id = u.id
                JOIN `lms_analytics.groups` g ON e.group_id = g.id
                LEFT JOIN `lms_analytics.attendances` a ON e.id = a.enrollment_id
                LEFT JOIN `lms_analytics.class_sessions` cs ON a.class_session_id = cs.id
                WHERE g.status = 'active'
                  AND e.academic_status = 'active'
                  AND e.payment_status = 'paid'
                  {$whereConditions['attendance_where']}  -- ✅ FILTROS SEPARADOS
                GROUP BY e.id, e.user_id, u.name, g.name
            ),
            student_grades AS (
                SELECT 
                    e.id as enrollment_id,
                    AVG(gr.grade) as avg_grade,
                    CASE 
                        WHEN AVG(gr.grade) >= 11 THEN 'Aprobado'
                        ELSE 'Reprobado'
                    END as academic_status,
                    COUNT(gr.id) as total_exams
                FROM `lms_analytics.enrollments` e
                JOIN `lms_analytics.groups` g ON e.group_id = g.id
                LEFT JOIN `lms_analytics.grades` gr ON e.id = gr.enrollment_id
                LEFT JOIN `lms_analytics.exams` ex ON gr.exam_id = ex.id
                WHERE g.status = 'active'
                  AND e.academic_status = 'active'
                  AND e.payment_status = 'paid'
                  {$whereConditions['grades_where']}  -- ✅ FILTROS SEPARADOS
                GROUP BY e.id
            )
            SELECT 
                sa.student_name,
                sa.group_name,
                sa.attendance_rate,
                sg.avg_grade,
                sg.academic_status,
                sg.total_exams,
                sa.total_sessions,
                sa.present_count
            FROM student_attendance sa
            JOIN student_grades sg ON sa.enrollment_id = sg.enrollment_id
            WHERE sa.attendance_rate IS NOT NULL 
                AND sg.avg_grade IS NOT NULL
                AND sa.total_sessions > 0
            ORDER BY sa.attendance_rate DESC
        ";

        $cacheKey = $this->generateCacheKey('attendance_grade_correlation', $filters);

        try {
            $results = $this->executeCachedQuery($query, $cacheKey);

            $correlation = count($results) > 1 ? $this->calculateCorrelation($results) : 0;
            $approvalStats = $this->calculateApprovalStats($results);

            return [
                'scatter_data' => $results,
                'correlation' => $correlation,
                'approval_stats' => $approvalStats,
                'summary' => $this->calculateCorrelationSummary($results),
                'filters_applied' => $this->getChartFiltersSummary($filters),
                'query_debug' => $this->getQueryDebugInfo($filters, $whereConditions) // ✅ DEBUG
            ];
        } catch (\Exception $e) {
            Log::error('Error en getAttendanceGradeCorrelation: ' . $e->getMessage());
            return $this->getDefaultCorrelationData();
        }
    }

    /**
     * Rendimiento por Grupo (Gráfico Radar)
     */
    public function getGroupPerformanceRadar(array $filters = []): array
    {
        $whereConditions = $this->buildGroupPerformanceWhereClause($filters);

        $query = "
            SELECT 
                g.name as group_name,
                c.name as course_name,
                COUNT(DISTINCT e.id) as total_students,
                AVG(er.final_grade) as avg_final_grade,
                AVG(er.attendance_percentage) as avg_attendance,
                COUNT(DISTINCT CASE WHEN er.final_grade >= 11 THEN e.id END) as approved_students,
                COUNT(DISTINCT CASE WHEN er.final_grade < 11 THEN e.id END) as failed_students,
                CASE 
                    WHEN COUNT(DISTINCT e.id) > 0 THEN 
                        ROUND(COUNT(DISTINCT CASE WHEN er.final_grade >= 11 THEN e.id END) * 100.0 / COUNT(DISTINCT e.id), 2)
                    ELSE 0 
                END as approval_rate,
                CASE 
                    WHEN AVG(er.final_grade) IS NOT NULL THEN 
                        ROUND((AVG(er.final_grade) * 100.0 / 20), 2)
                    ELSE 0 
                END as performance_score
            FROM `lms_analytics.groups` g
            LEFT JOIN `lms_analytics.course_versions` cv ON g.course_version_id = cv.id
            LEFT JOIN `lms_analytics.courses` c ON cv.course_id = c.id
            LEFT JOIN `lms_analytics.enrollments` e ON g.id = e.group_id
            LEFT JOIN `lms_analytics.enrollment_results` er ON e.id = er.enrollment_id
            LEFT JOIN `lms_analytics.grades` gr ON e.id = gr.enrollment_id
            LEFT JOIN `lms_analytics.exams` ex ON gr.exam_id = ex.id
            LEFT JOIN `lms_analytics.attendances` a ON e.id = a.enrollment_id
            LEFT JOIN `lms_analytics.class_sessions` cs ON a.class_session_id = cs.id
            WHERE g.status = 'active' 
              AND e.academic_status = 'active'
              AND e.payment_status = 'paid'
              {$whereConditions}  -- ✅ FILTROS APLICADOS
            GROUP BY g.id, g.name, c.name
            ORDER BY approval_rate DESC
        ";

        $cacheKey = $this->generateCacheKey('group_performance_radar', $filters);

        try {
            $results = $this->executeCachedQuery($query, $cacheKey);
            return [
                'group_performance' => $results,
                'filters_applied' => $this->getChartFiltersSummary($filters),
                'query_debug' => $this->getQueryDebugInfo($filters, $whereConditions) // ✅ DEBUG
            ];
        } catch (\Exception $e) {
            Log::error('Error en getGroupPerformanceRadar: ' . $e->getMessage());
            return [
                'group_performance' => [],
                'filters_applied' => []
            ];
        }
    }

    /**
     * WHERE clause específico para rendimiento por grupo
     */
    private function buildGroupPerformanceWhereClause(array $filters): string
    {
        $conditions = [];

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'group_id':
                    $conditions[] = "g.id = " . (int)$value;
                    break;
                case 'course_version_id':
                    $conditions[] = "g.course_version_id = " . (int)$value;
                    break;
                case 'start_date':
                    // ✅ Filtro CORRECTO que aplica a ambas tablas
                    $conditions[] = "(ex.start_time >= TIMESTAMP('{$value}') OR cs.start_time >= TIMESTAMP('{$value}'))";
                    break;
                case 'end_date':
                    $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                    $conditions[] = "(ex.start_time < TIMESTAMP('{$nextDay}') OR cs.start_time < TIMESTAMP('{$nextDay}'))";
                    break;
                case 'exam_start_date':
                    $conditions[] = "ex.start_time >= TIMESTAMP('{$value}')";
                    break;
                case 'attendance_start_date':
                    $conditions[] = "cs.start_time >= TIMESTAMP('{$value}')";
                    break;
            }
        }

        return $conditions ? ' AND ' . implode(' AND ', $conditions) : '';
    }

    /**
     * WHERE clause específico para correlación asistencia-calificación
     */
    private function buildAttendanceGradeCorrelationWhereClause(array $filters): array
    {
        $attendanceConditions = [];
        $gradesConditions = [];

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'group_id':
                    $attendanceConditions[] = "g.id = " . (int)$value;
                    $gradesConditions[] = "g.id = " . (int)$value;
                    break;
                case 'course_version_id':
                    $attendanceConditions[] = "g.course_version_id = " . (int)$value;
                    $gradesConditions[] = "g.course_version_id = " . (int)$value;
                    break;
                case 'start_date':
                    // ✅ Filtro CORRECTO para asistencia
                    $attendanceConditions[] = "cs.start_time >= TIMESTAMP('{$value}')";
                    // ✅ Filtro CORRECTO para calificaciones
                    $gradesConditions[] = "ex.start_time >= TIMESTAMP('{$value}')";
                    break;
                case 'end_date':
                    $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                    $attendanceConditions[] = "cs.start_time < TIMESTAMP('{$nextDay}')";
                    $gradesConditions[] = "ex.start_time < TIMESTAMP('{$nextDay}')";
                    break;
                case 'attendance_start_date':
                    $attendanceConditions[] = "cs.start_time >= TIMESTAMP('{$value}')";
                    break;
                case 'attendance_end_date':
                    $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                    $attendanceConditions[] = "cs.start_time < TIMESTAMP('{$nextDay}')";
                    break;
                case 'exam_start_date':
                    $gradesConditions[] = "ex.start_time >= TIMESTAMP('{$value}')";
                    break;
                case 'exam_end_date':
                    $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                    $gradesConditions[] = "ex.start_time < TIMESTAMP('{$nextDay}')";
                    break;
            }
        }

        return [
            'attendance_where' => $attendanceConditions ? ' AND ' . implode(' AND ', $attendanceConditions) : '',
            'grades_where' => $gradesConditions ? ' AND ' . implode(' AND ', $gradesConditions) : ''
        ];
    }

    /**
     * Información de debug para queries
     */
    private function getQueryDebugInfo(array $filters, $whereConditions): array
    {
        return [
            'filters_received' => $filters,
            'where_conditions_applied' => $whereConditions,
            'date_range_expected' => [
                'start_date' => $filters['start_date'] ?? 'No aplicado',
                'end_date' => $filters['end_date'] ?? 'No aplicado'
            ]
        ];
    }

    /**
     * Genera resumen de filtros para gráficas
     */
    private function getChartFiltersSummary(array $filters): array
    {
        $summary = [];

        if (isset($filters['group_id'])) {
            $summary[] = "Grupo ID: {$filters['group_id']}";
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $summary[] = "Período: {$filters['start_date']} a {$filters['end_date']}";
        }

        return $summary;
    }

    /**
     * Calcula estadísticas de aprobación
     */
    private function calculateApprovalStats(array $scatterData): array
    {
        if (empty($scatterData)) {
            return ['approved' => 0, 'failed' => 0, 'approval_rate' => 0];
        }

        $approved = 0;
        $failed = 0;

        foreach ($scatterData as $student) {
            if ($student['avg_grade'] >= 11) {
                $approved++;
            } else {
                $failed++;
            }
        }

        $total = $approved + $failed;
        $approvalRate = $total > 0 ? round(($approved / $total) * 100, 2) : 0;

        return [
            'approved' => $approved,
            'failed' => $failed,
            'approval_rate' => $approvalRate
        ];
    }

    /**
     * Calcula resumen de correlación
     */
    private function calculateCorrelationSummary(array $results): array
    {
        return [
            'total_students' => count($results),
            'avg_attendance' => count($results) > 0 ? round(array_sum(array_column($results, 'attendance_rate')) / count($results), 2) : 0,
            'avg_grade' => count($results) > 0 ? round(array_sum(array_column($results, 'avg_grade')) / count($results), 2) : 0,
            'passing_grade' => 11
        ];
    }

    /**
     * Calcula estadísticas de calificaciones
     */
    private function calculateGradeStats(array $filters): array
    {
        $whereConditions = $this->buildGradeDistributionWhereClause($filters);

        $query = "
        SELECT 
            COUNT(DISTINCT e.id) as total_students,
            AVG(gr.grade) as avg_grade,
            COUNT(DISTINCT CASE WHEN gr.grade >= 11 THEN e.id END) as approved_students,
            CASE 
                WHEN COUNT(DISTINCT e.id) > 0 THEN 
                    ROUND(COUNT(DISTINCT CASE WHEN gr.grade >= 11 THEN e.id END) * 100.0 / COUNT(DISTINCT e.id), 2)
                ELSE 0 
            END as approval_rate
        FROM `lms_analytics.grades` gr
        JOIN `lms_analytics.enrollments` e ON gr.enrollment_id = e.id
        JOIN `lms_analytics.groups` g ON e.group_id = g.id
        LEFT JOIN `lms_analytics.exams` ex ON gr.exam_id = ex.id
        WHERE gr.grade IS NOT NULL 
          AND g.status = 'active'
          AND e.academic_status = 'active'
          AND e.payment_status = 'paid'
          {$whereConditions}
    ";

        try {
            $results = $this->executeCachedQuery($query, 'grade_stats_' . md5(serialize($filters)), 60);
            return $results[0] ?? $this->getDefaultGradeStats();
        } catch (\Exception $e) {
            Log::error('Error en calculateGradeStats: ' . $e->getMessage());
            return $this->getDefaultGradeStats();
        }
    }

    /**
     * Datos por defecto para estadísticas de calificaciones
     */
    private function getDefaultGradeStats(): array
    {
        return [
            'total_students' => 0,
            'avg_grade' => 0,
            'approved_students' => 0,
            'approval_rate' => 0
        ];
    }

    /**
     * Datos por defecto para correlación
     */
    private function getDefaultCorrelationData(): array
    {
        return [
            'scatter_data' => [],
            'correlation' => 0,
            'approval_stats' => ['approved' => 0, 'failed' => 0, 'approval_rate' => 0],
            'summary' => [
                'total_students' => 0,
                'avg_attendance' => 0,
                'avg_grade' => 0,
                'passing_grade' => 11
            ]
        ];
    }
}
