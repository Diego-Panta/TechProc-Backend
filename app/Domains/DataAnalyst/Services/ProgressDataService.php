<?php
// app/Domains/DataAnalyst/Services/ProgressDataService.php

namespace App\Domains\DataAnalyst\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Support\Facades\Log;

class ProgressDataService
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
     * Métricas de progreso COMPLETAS con filtros de fecha
     */
    public function getProgressMetricsWithFilters(array $filters = []): array
    {
        $moduleCompletion = $this->getModuleCompletionWithFilters($filters);
        $gradeConsistency = $this->getGradeConsistencyWithFilters($filters);

        return [
            'module_completion' => $moduleCompletion,
            'grade_consistency' => $gradeConsistency,
            'summary' => $this->calculateProgressSummary($moduleCompletion, $gradeConsistency),
            'filters_applied' => $this->getDateFiltersSummary($filters),
            'data_range_info' => $this->getDataRangeInfo($filters)
        ];
    }

    /**
     * Completitud de módulos con filtros de fecha
     */
    private function getModuleCompletionWithFilters(array $filters): array
    {
        $whereConditions = $this->buildModuleCompletionWhereClause($filters);

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
                m.id as module_id,
                COALESCE(m.title, 'Módulo sin título') as module_title,
                m.sort as module_order,
                COUNT(DISTINCT cs.id) as total_sessions,
                COUNT(DISTINCT CASE WHEN a.status = 'present' THEN cs.id END) as attended_sessions,
                0 as completion_days, -- Siempre 0 para métrica de progreso
                CASE 
                    WHEN COUNT(DISTINCT cs.id) > 0 THEN
                        ROUND((COUNT(DISTINCT CASE WHEN a.status = 'present' THEN cs.id END) * 100.0 / COUNT(DISTINCT cs.id)), 2)
                    ELSE 0
                END as completion_rate
            FROM `lms_analytics.enrollments` e
            JOIN `lms_analytics.groups` g ON e.group_id = g.id
            LEFT JOIN `lms_analytics.users` u ON e.user_id = u.id
            LEFT JOIN `lms_analytics.course_versions` cv ON g.course_version_id = cv.id
            LEFT JOIN `lms_analytics.courses` c ON cv.course_id = c.id
            LEFT JOIN `lms_analytics.modules` m ON g.course_version_id = m.course_version_id
            LEFT JOIN `lms_analytics.class_sessions` cs ON m.id = cs.module_id AND g.id = cs.group_id
            LEFT JOIN `lms_analytics.attendances` a ON cs.id = a.class_session_id AND e.id = a.enrollment_id
            WHERE g.status = 'active' 
              AND e.academic_status = 'active'
              AND e.payment_status = 'paid'
              AND cs.id IS NOT NULL
              {$whereConditions}
            GROUP BY e.id, e.user_id, u.name, u.email, e.group_id, g.name, c.name, cv.name, m.id, m.title, m.sort
            HAVING total_sessions > 0
            ORDER BY e.id, m.sort
        ";

        try {
            $results = $this->executeQuery($query);
            return $this->formatModuleCompletionResults($results);
        } catch (\Exception $e) {
            Log::error('Error en getModuleCompletionWithFilters: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Consistencia de calificaciones con filtros de fecha
     */
    private function getGradeConsistencyWithFilters(array $filters): array
    {
        $whereConditions = $this->buildGradeConsistencyWhereClause($filters);

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
                COUNT(gr.id) as total_grades,
                ROUND(AVG(gr.grade), 2) as avg_grade,
                ROUND(STDDEV(gr.grade), 2) as grade_stddev,
                ROUND(MIN(gr.grade), 2) as min_grade,
                ROUND(MAX(gr.grade), 2) as max_grade
            FROM `lms_analytics.enrollments` e
            JOIN `lms_analytics.groups` g ON e.group_id = g.id
            LEFT JOIN `lms_analytics.users` u ON e.user_id = u.id
            LEFT JOIN `lms_analytics.course_versions` cv ON g.course_version_id = cv.id
            LEFT JOIN `lms_analytics.courses` c ON cv.course_id = c.id
            LEFT JOIN `lms_analytics.grades` gr ON e.id = gr.enrollment_id
            LEFT JOIN `lms_analytics.exams` ex ON gr.exam_id = ex.id
            WHERE g.status = 'active' 
              AND e.academic_status = 'active'
              AND e.payment_status = 'paid'
              {$whereConditions}
            GROUP BY e.id, e.user_id, u.name, u.email, e.group_id, g.name, c.name, cv.name
            HAVING total_grades > 0
            ORDER BY e.id
        ";

        try {
            $results = $this->executeQuery($query);
            return $this->formatGradeConsistencyResults($results);
        } catch (\Exception $e) {
            Log::error('Error en getGradeConsistencyWithFilters: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * WHERE clause para completitud de módulos
     */
    private function buildModuleCompletionWhereClause(array $filters): string
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
                    // Filtro por fecha de clase
                    $conditions[] = "cs.start_time >= TIMESTAMP('{$value}')";
                    break;
                case 'end_date':
                    $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                    $conditions[] = "cs.start_time < TIMESTAMP('{$nextDay}')";
                    break;
                case 'module_id':
                    $conditions[] = "m.id = " . (int)$value;
                    break;
            }
        }

        return $conditions ? ' AND ' . implode(' AND ', $conditions) : '';
    }

    /**
     * WHERE clause para consistencia de calificaciones
     */
    private function buildGradeConsistencyWhereClause(array $filters): string
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
                    // Filtro por fecha de examen
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
     * Calcula resumen de progreso
     */
    private function calculateProgressSummary(array $moduleCompletion, array $gradeConsistency): array
    {
        $summary = [
            'avg_completion_rate' => 0,
            'avg_grade' => 0,
            'total_students' => count(array_unique(array_column($moduleCompletion, 'enrollment_id'))),
            'total_modules' => count($moduleCompletion),
            'total_grades' => array_sum(array_column($gradeConsistency, 'total_grades'))
        ];

        // Calcular promedio de completitud
        if (!empty($moduleCompletion)) {
            $completionRates = array_column($moduleCompletion, 'completion_rate');
            $validRates = array_filter($completionRates, function($rate) {
                return $rate !== null && is_numeric($rate);
            });
            
            if (!empty($validRates)) {
                $summary['avg_completion_rate'] = round(array_sum($validRates) / count($validRates), 2);
            }
        }

        // Calcular promedio de calificaciones
        if (!empty($gradeConsistency)) {
            $avgGrades = array_column($gradeConsistency, 'avg_grade');
            $validGrades = array_filter($avgGrades, function($grade) {
                return $grade !== null && is_numeric($grade);
            });
            
            if (!empty($validGrades)) {
                $summary['avg_grade'] = round(array_sum($validGrades) / count($validGrades), 2);
            }
        }

        return $summary;
    }

    /**
     * Formatea resultados de completitud de módulos
     */
    private function formatModuleCompletionResults(array $results): array
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
                'module_id' => $row['module_id'] ?? 0,
                'module_title' => $row['module_title'] ?? '',
                'module_order' => $row['module_order'] ?? 0,
                'total_sessions' => $row['total_sessions'] ?? 0,
                'attended_sessions' => $row['attended_sessions'] ?? 0,
                'completion_rate' => $row['completion_rate'] ?? 0.0,
                'completion_days' => $row['completion_days'] ?? 0
            ];
        }
        return $formatted;
    }

    /**
     * Formatea resultados de consistencia de calificaciones
     */
    private function formatGradeConsistencyResults(array $results): array
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
                'total_grades' => $row['total_grades'] ?? 0,
                'avg_grade' => $row['avg_grade'] ?? 0.0,
                'grade_stddev' => $row['grade_stddev'] ?? 0.0,
                'min_grade' => $row['min_grade'] ?? 0.0,
                'max_grade' => $row['max_grade'] ?? 0.0
            ];
        }
        return $formatted;
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
    private function getDateFiltersSummary(array $filters): array
    {
        $summary = [];
        
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $summary[] = "Período: {$filters['start_date']} a {$filters['end_date']}";
        } elseif (isset($filters['start_date'])) {
            $summary[] = "Desde: {$filters['start_date']}";
        } elseif (isset($filters['end_date'])) {
            $summary[] = "Hasta: {$filters['end_date']}";
        }
        
        if (isset($filters['group_id'])) {
            $summary[] = "Grupo ID: {$filters['group_id']}";
        }
        
        if (isset($filters['course_version_id'])) {
            $summary[] = "Versión de curso ID: {$filters['course_version_id']}";
        }
        
        return empty($summary) ? ['Sin filtros aplicados'] : $summary;
    }

    /**
     * Información del rango de datos
     */
    private function getDataRangeInfo(array $filters): array
    {
        $info = [
            'date_filters_applied' => isset($filters['start_date']) || isset($filters['end_date']),
            'scope' => 'Período específico'
        ];
        
        if (!isset($filters['start_date']) && !isset($filters['end_date'])) {
            $info['scope'] = 'Todos los datos acumulados';
        }
        
        return $info;
    }
}