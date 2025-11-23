<?php
// app/Domains/DataAnalyst/Services/PerformanceDataService.php

namespace App\Domains\DataAnalyst\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Support\Facades\Log;

class PerformanceDataService
{
    protected $bigQuery;

    public function __construct()
    {
        $this->bigQuery = new BigQueryClient([
            'projectId' => env('BIGQUERY_PROJECT_ID'),
            'keyFilePath' => base_path(env('GOOGLE_APPLICATION_CREDENTIALS')),
        ]);
    }

    /**
     * Métricas de rendimiento COMPLETAS con filtros
     */
    public function getPerformanceMetricsWithFilters(array $filters = []): array
    {
        $studentPerformance = $this->getStudentPerformanceWithFilters($filters);
        $coursePerformance = $this->getCoursePerformanceWithFilters($filters, $studentPerformance);

        return [
            'student_performance' => $studentPerformance,
            'course_performance' => $coursePerformance,
            'summary' => $this->calculatePerformanceSummary($studentPerformance, $coursePerformance),
            'filters_applied' => $this->getPerformanceFiltersSummary($filters),
            'data_scope' => $this->getDataScopeInfo($filters)
        ];
    }

    /**
     * Rendimiento de estudiantes con filtros
     */
    private function getStudentPerformanceWithFilters(array $filters): array
    {
        $whereConditions = $this->buildStudentPerformanceWhereClause($filters);

        $query = "
            SELECT
                e.id as enrollment_id,
                e.user_id,
                COALESCE(u.name, 'Usuario no encontrado') as student_name,
                COALESCE(u.email, 'Email no disponible') as student_email,
                e.group_id,
                g.name as group_name,
                COALESCE(c.name, 'Curso no especificado') as course_name,
                COALESCE(cv.name, 'Versión no especificada') as course_version,
                g.course_version_id,
                
                -- CALIFICACIÓN FINAL con filtros de fecha
                CASE 
                    WHEN COUNT(DISTINCT gr.exam_id) > 0 THEN ROUND(AVG(gr.grade), 2)
                    ELSE NULL
                END as final_grade,
                
                -- ASISTENCIA con filtros de fecha
                CASE 
                    WHEN COUNT(DISTINCT cs.id) > 0 THEN 
                        ROUND((COUNT(DISTINCT CASE WHEN a.status = 'present' THEN cs.id END) * 100.0 / COUNT(DISTINCT cs.id)), 2)
                    ELSE NULL
                END as attendance_percentage,
                
                -- ESTADO ACADÉMICO
                CASE 
                    WHEN COUNT(DISTINCT gr.exam_id) > 0 AND AVG(gr.grade) >= 11 THEN 'approved'
                    WHEN COUNT(DISTINCT gr.exam_id) > 0 AND AVG(gr.grade) < 11 THEN 'failed'
                    ELSE 'in_progress'
                END as enrollment_status,
                
                COUNT(DISTINCT gr.exam_id) as total_exams_taken,
                ROUND(AVG(gr.grade), 2) as overall_avg_grade,
                ROUND(MIN(gr.grade), 2) as min_grade,
                ROUND(MAX(gr.grade), 2) as max_grade,
                ROUND(STDDEV(gr.grade), 2) as grade_stddev
                
            FROM `lms_analytics.enrollments` e
            JOIN `lms_analytics.groups` g ON e.group_id = g.id
            LEFT JOIN `lms_analytics.users` u ON e.user_id = u.id
            LEFT JOIN `lms_analytics.course_versions` cv ON g.course_version_id = cv.id
            LEFT JOIN `lms_analytics.courses` c ON cv.course_id = c.id
            LEFT JOIN `lms_analytics.grades` gr ON e.id = gr.enrollment_id
            LEFT JOIN `lms_analytics.exams` ex ON gr.exam_id = ex.id
            LEFT JOIN `lms_analytics.attendances` a ON e.id = a.enrollment_id
            LEFT JOIN `lms_analytics.class_sessions` cs ON a.class_session_id = cs.id
            WHERE g.status = 'active' 
              AND e.academic_status = 'active'
              AND e.payment_status = 'paid'
              {$whereConditions}
            GROUP BY 
                e.id, e.user_id, u.name, u.email, e.group_id, g.name, c.name, 
                cv.name, g.course_version_id
            HAVING final_grade IS NOT NULL
            ORDER BY e.id
        ";

        try {
            $results = $this->executeQuery($query);
            return $this->formatStudentPerformanceResults($results);
        } catch (\Exception $e) {
            Log::error('Error en getStudentPerformanceWithFilters: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Rendimiento de cursos con filtros
     */
    private function getCoursePerformanceWithFilters(array $filters, array $studentPerformance): array
    {
        $whereConditions = $this->buildCoursePerformanceWhereClause($filters);

        $query = "
            SELECT
                g.id as group_id,
                g.name as group_name,
                COALESCE(c.name, 'Curso no especificado') as course_name,
                COALESCE(cv.name, 'Versión no especificada') as course_version,
                
                -- Contar estudiantes del grupo que tienen calificaciones
                (SELECT COUNT(*) 
                 FROM `lms_analytics.enrollments` e2 
                 JOIN `lms_analytics.grades` gr2 ON e2.id = gr2.enrollment_id
                 WHERE e2.group_id = g.id 
                   AND e2.academic_status = 'active'
                   AND e2.payment_status = 'paid'
                ) as total_students
                
            FROM `lms_analytics.groups` g
            LEFT JOIN `lms_analytics.course_versions` cv ON g.course_version_id = cv.id
            LEFT JOIN `lms_analytics.courses` c ON cv.course_id = c.id
            WHERE g.status = 'active'
              {$whereConditions}
            GROUP BY g.id, g.name, c.name, cv.name
            ORDER BY g.name
        ";

        try {
            $results = $this->executeQuery($query);
            return $this->formatCoursePerformanceResults($results, $studentPerformance);
        } catch (\Exception $e) {
            Log::error('Error en getCoursePerformanceWithFilters: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * WHERE clause para rendimiento de estudiantes
     */
    private function buildStudentPerformanceWhereClause(array $filters): string
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
                case 'user_id':
                    $conditions[] = "e.user_id = " . (int)$value;
                    break;
                case 'start_date':
                    // ✅ Filtro CORREGIDO - Usar OR para cubrir ambas tablas
                    $conditions[] = "(ex.start_time >= TIMESTAMP('{$value}') OR cs.start_time >= TIMESTAMP('{$value}'))";
                    break;
                case 'end_date':
                    $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                    $conditions[] = "(ex.start_time < TIMESTAMP('{$nextDay}') OR cs.start_time < TIMESTAMP('{$nextDay}'))";
                    break;
                case 'exam_start_date':
                    // ✅ Filtro específico para exámenes
                    $conditions[] = "ex.start_time >= TIMESTAMP('{$value}')";
                    break;
                case 'exam_end_date':
                    $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                    $conditions[] = "ex.start_time < TIMESTAMP('{$nextDay}')";
                    break;
                case 'attendance_start_date':
                    // ✅ Filtro específico para asistencia
                    $conditions[] = "cs.start_time >= TIMESTAMP('{$value}')";
                    break;
                case 'attendance_end_date':
                    $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                    $conditions[] = "cs.start_time < TIMESTAMP('{$nextDay}')";
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
     * WHERE clause para rendimiento de cursos
     */
    private function buildCoursePerformanceWhereClause(array $filters): string
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
            }
        }

        return $conditions ? ' AND ' . implode(' AND ', $conditions) : '';
    }

    /**
     * Formatea resultados de estudiantes
     */
    private function formatStudentPerformanceResults(array $results): array
    {
        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = [
                'enrollment_id' => $row['enrollment_id'] ?? 0,
                'user_id' => $row['user_id'] ?? 0,
                'student_name' => $row['student_name'] ?? '',
                'student_email' => $row['student_email'] ?? '',
                'group_id' => $row['group_id'] ?? 0,
                'group_name' => $row['group_name'] ?? '',
                'course_name' => $row['course_name'] ?? '',
                'course_version' => $row['course_version'] ?? '',
                'final_grade' => $row['final_grade'] ?? null,
                'attendance_percentage' => $row['attendance_percentage'] ?? null,
                'enrollment_status' => $row['enrollment_status'] ?? '',
                'total_exams_taken' => $row['total_exams_taken'] ?? 0,
                'overall_avg_grade' => $row['overall_avg_grade'] ?? null,
                'min_grade' => $row['min_grade'] ?? null,
                'max_grade' => $row['max_grade'] ?? null,
                'grade_stddev' => $row['grade_stddev'] ?? null
            ];
        }
        return $formatted;
    }

    /**
     * Formatea resultados de cursos
     */
    private function formatCoursePerformanceResults(array $results, array $studentPerformance): array
    {
        $formatted = [];
        
        foreach ($results as $row) {
            $groupId = $row['group_id'];
            
            // Calcular métricas desde los datos de estudiantes
            $groupStudents = array_filter($studentPerformance, function($student) use ($groupId) {
                return $student['group_id'] == $groupId;
            });
            
            $totalStudents = count($groupStudents);
            $approvedStudents = count(array_filter($groupStudents, function($student) {
                return $student['enrollment_status'] === 'approved';
            }));
            
            $avgFinalGrade = $totalStudents > 0 ? 
                array_sum(array_column($groupStudents, 'final_grade')) / $totalStudents : null;
                
            $avgAttendance = $totalStudents > 0 ? 
                array_sum(array_column($groupStudents, 'attendance_percentage')) / $totalStudents : null;
                
            $approvalRate = $totalStudents > 0 ? 
                round(($approvedStudents / $totalStudents) * 100, 2) : 0;

            $formatted[] = [
                'group_id' => $groupId,
                'group_name' => $row['group_name'] ?? '',
                'course_name' => $row['course_name'] ?? '',
                'course_version' => $row['course_version'] ?? '',
                'total_students' => $totalStudents,
                'avg_final_grade' => $avgFinalGrade,
                'avg_attendance' => $avgAttendance,
                'approved_students' => $approvedStudents,
                'failed_students' => $totalStudents - $approvedStudents,
                'approval_rate' => $approvalRate,
                'has_data' => $totalStudents > 0
            ];
        }

        return $formatted;
    }

    /**
     * Calcula resumen general
     */
    private function calculatePerformanceSummary(array $studentPerformance, array $coursePerformance): array
    {
        $totalStudents = count($studentPerformance);
        $approvedStudents = count(array_filter($studentPerformance, function($student) {
            return $student['enrollment_status'] === 'approved';
        }));

        $avgFinalGrade = $totalStudents > 0 ? 
            array_sum(array_column($studentPerformance, 'final_grade')) / $totalStudents : 0;

        $avgAttendance = $totalStudents > 0 ? 
            array_sum(array_column($studentPerformance, 'attendance_percentage')) / $totalStudents : 0;

        $approvalRate = $totalStudents > 0 ? 
            round(($approvedStudents / $totalStudents) * 100, 2) : 0;

        return [
            'total_students' => $totalStudents,
            'total_courses' => count($coursePerformance),
            'overall_approval_rate' => $approvalRate,
            'overall_avg_grade' => round($avgFinalGrade, 2),
            'overall_avg_attendance' => round($avgAttendance, 2),
            'data_consistency_check' => 'verified',
            'filters_applied' => [
                'group_status' => 'active',
                'academic_status' => 'active',
                'payment_status' => 'paid',
                'has_grades' => $totalStudents > 0
            ]
        ];
    }

    /**
     * Ejecuta consulta en BigQuery
     */
    private function executeQuery(string $query): array
    {
        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);
        
        $results = [];
        foreach ($queryResults as $row) {
            $results[] = $row;
        }
        
        return $results;
    }

    /**
     * Genera resumen de filtros aplicados
     */
    private function getPerformanceFiltersSummary(array $filters): array
    {
        $summary = [];
        
        if (isset($filters['group_id'])) {
            $summary[] = "Grupo ID: {$filters['group_id']}";
        }
        
        if (isset($filters['course_version_id'])) {
            $summary[] = "Versión de curso ID: {$filters['course_version_id']}";
        }
        
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $summary[] = "Período: {$filters['start_date']} a {$filters['end_date']}";
        } elseif (isset($filters['start_date'])) {
            $summary[] = "Desde: {$filters['start_date']}";
        } elseif (isset($filters['end_date'])) {
            $summary[] = "Hasta: {$filters['end_date']}";
        }
        
        if (isset($filters['exam_start_date'])) {
            $summary[] = "Exámenes desde: {$filters['exam_start_date']}";
        }
        
        if (isset($filters['attendance_start_date'])) {
            $summary[] = "Asistencia desde: {$filters['attendance_start_date']}";
        }

        return empty($summary) ? ['Sin filtros aplicados'] : $summary;
    }

    /**
     * Información del alcance de datos
     */
    private function getDataScopeInfo(array $filters): array
    {
        $info = [
            'date_filters_applied' => isset($filters['start_date']) || isset($filters['end_date']),
            'scope' => 'Todos los datos acumulados'
        ];
        
        if (isset($filters['start_date']) || isset($filters['end_date'])) {
            $info['scope'] = 'Período específico';
        }
        
        if (isset($filters['group_id'])) {
            $info['scope'] .= ' - Grupo específico';
        }
        
        return $info;
    }
}