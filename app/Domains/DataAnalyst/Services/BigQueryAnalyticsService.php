<?php
// app/Domains/DataAnalyst/Services/BigQueryAnalyticsService.php

namespace App\Domains\DataAnalyst\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BigQueryAnalyticsService
{
    protected $bigQuery;
    protected $dataset;
    protected $cacheTTL;

    public function __construct()
    {
        $this->bigQuery = new BigQueryClient([
            'projectId' => env('BIGQUERY_PROJECT_ID'),
            'keyFilePath' => base_path(env('GOOGLE_APPLICATION_CREDENTIALS')),
        ]);

        $this->dataset = $this->bigQuery->dataset('lms_analytics');
        $this->cacheTTL = 300;
    }

    protected function executeCachedQuery(string $query, string $cacheKey, int $ttl = null): array
    {
        $ttl = $ttl ?? $this->cacheTTL;

        return Cache::remember($cacheKey, $ttl, function () use ($query) {
            $queryJobConfig = $this->bigQuery->query($query);
            $queryResults = $this->bigQuery->runQuery($queryJobConfig);
            
            $results = [];
            foreach ($queryResults as $row) {
                $results[] = $row;
            }
            
            return $results;
        });
    }

    /**
     * Métricas de asistencia optimizadas - CORREGIDO con JOINs dinámicos
     */
    public function getAttendanceMetrics(array $filters = []): array
    {
        $joinConditions = $this->buildJoinClause($filters);
        $whereConditions = $this->buildWhereClause($filters);
        
        $query = "
            WITH attendance_stats AS (
                SELECT 
                    e.id as enrollment_id,
                    e.user_id,
                    e.group_id,
                    g.course_version_id,
                    g.name as group_name,
                    COUNT(a.id) as total_sessions,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count
                FROM `lms_analytics.enrollments` e
                JOIN `lms_analytics.groups` g ON e.group_id = g.id
                JOIN `lms_analytics.attendances` a ON e.id = a.enrollment_id
                JOIN `lms_analytics.class_sessions` cs ON a.class_session_id = cs.id
                {$joinConditions}
                WHERE 1=1 {$whereConditions}
                GROUP BY e.id, e.user_id, e.group_id, g.course_version_id, g.name
            ),
            group_attendance AS (
                SELECT
                    group_id,
                    group_name,
                    COUNT(enrollment_id) as total_students,
                    AVG(SAFE_DIVIDE(present_count, total_sessions)) * 100 as avg_attendance_rate,
                    AVG(SAFE_DIVIDE(absent_count, total_sessions)) * 100 as avg_absence_rate
                FROM attendance_stats
                WHERE total_sessions > 0
                GROUP BY group_id, group_name
            ),
            weekly_patterns AS (
                SELECT
                    EXTRACT(WEEK FROM cs.start_time) as week_number,
                    EXTRACT(YEAR FROM cs.start_time) as year,
                    COUNT(a.id) as total_attendances,
                    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absences,
                    SAFE_DIVIDE(
                        SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END), 
                        COUNT(a.id)
                    ) * 100 as absence_rate
                FROM `lms_analytics.class_sessions` cs
                JOIN `lms_analytics.attendances` a ON cs.id = a.class_session_id
                JOIN `lms_analytics.enrollments` e ON a.enrollment_id = e.id
                JOIN `lms_analytics.groups` g ON e.group_id = g.id
                {$joinConditions}
                WHERE 1=1 {$whereConditions}
                GROUP BY week_number, year
                ORDER BY year, week_number
            )
            
            SELECT 
                'student_level' as metric_type,
                TO_JSON_STRING(ARRAY_AGG(STRUCT(
                    enrollment_id,
                    user_id,
                    group_id,
                    group_name,
                    total_sessions,
                    present_count,
                    absent_count,
                    late_count,
                    SAFE_DIVIDE(present_count, total_sessions) * 100 as attendance_rate
                ))) as data
            FROM attendance_stats
            WHERE total_sessions > 0
            
            UNION ALL
            
            SELECT 
                'group_level' as metric_type,
                TO_JSON_STRING(ARRAY_AGG(STRUCT(
                    group_id,
                    group_name,
                    total_students,
                    avg_attendance_rate,
                    avg_absence_rate
                ))) as data
            FROM group_attendance
            
            UNION ALL
            
            SELECT 
                'weekly_patterns' as metric_type,
                TO_JSON_STRING(ARRAY_AGG(STRUCT(
                    week_number,
                    year,
                    total_attendances,
                    absences,
                    absence_rate
                ))) as data
            FROM weekly_patterns
            WHERE total_attendances > 0
        ";

        $cacheKey = 'attendance_metrics_' . md5(serialize($filters));
        
        try {
            $results = $this->executeCachedQuery($query, $cacheKey);
            return $this->formatAttendanceResults($results);
        } catch (\Exception $e) {
            // Log the actual query for debugging
            Log::error('BigQuery Error - Query: ' . $query);
            Log::error('BigQuery Error - Filters: ' . json_encode($filters));
            throw $e;
        }
    }

    /**
     * Construye cláusulas JOIN adicionales basadas en filtros
     */
    private function buildJoinClause(array $filters): string
    {
        $joins = [];

        if (isset($filters['module_id'])) {
            $joins[] = "JOIN `lms_analytics.modules` m ON cs.module_id = m.id";
        }

        if (isset($filters['course_version_id'])) {
            $joins[] = "JOIN `lms_analytics.course_versions` cv ON g.course_version_id = cv.id";
        }

        return implode(' ', $joins);
    }

    /**
     * Construye cláusula WHERE dinámica CORREGIDA
     */
    private function buildWhereClause(array $filters): string
    {
        $conditions = [];

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'group_id':
                    $conditions[] = "g.id = " . (int)$value;
                    break;
                case 'user_id':
                    $conditions[] = "e.user_id = " . (int)$value;
                    break;
                case 'course_version_id':
                    $conditions[] = "g.course_version_id = " . (int)$value;
                    break;
                case 'module_id':
                    $conditions[] = "m.id = " . (int)$value;
                    break;
                case 'start_date':
                    $conditions[] = "cs.start_time >= TIMESTAMP('{$value}')";
                    break;
                case 'end_date':
                    $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                    $conditions[] = "cs.start_time < TIMESTAMP('{$nextDay}')";
                    break;
            }
        }

        return $conditions ? ' AND ' . implode(' AND ', $conditions) : '';
    }

    /**
     * Métricas de progreso estudiantil - CORREGIDO
     */
    public function getProgressMetrics(array $filters = []): array
    {
        $joinConditions = $this->buildProgressJoinClause($filters);
        $whereConditions = $this->buildProgressWhereClause($filters);

        $query = "
            WITH module_completion AS (
                SELECT 
                    e.id as enrollment_id,
                    e.user_id,
                    e.group_id,
                    g.name as group_name,
                    m.id as module_id,
                    m.title as module_title,
                    COUNT(DISTINCT cs.id) as total_sessions,
                    COUNT(DISTINCT a.class_session_id) as attended_sessions,
                    MIN(cs.start_time) as first_session_date,
                    MAX(cs.start_time) as last_session_date,
                    DATE_DIFF(MAX(cs.start_time), MIN(cs.start_time), DAY) as completion_days
                FROM `lms_analytics.enrollments` e
                JOIN `lms_analytics.groups` g ON e.group_id = g.id
                JOIN `lms_analytics.modules` m ON g.course_version_id = m.course_version_id
                LEFT JOIN `lms_analytics.class_sessions` cs ON m.id = cs.module_id AND g.id = cs.group_id
                LEFT JOIN `lms_analytics.attendances` a ON cs.id = a.class_session_id AND e.id = a.enrollment_id
                {$joinConditions}
                WHERE 1=1 {$whereConditions}
                GROUP BY e.id, e.user_id, e.group_id, g.name, m.id, m.title
            ),
            grade_consistency AS (
                SELECT
                    e.id as enrollment_id,
                    e.user_id,
                    e.group_id,
                    g.name as group_name,
                    COUNT(gr.id) as total_grades,
                    AVG(gr.grade) as avg_grade,
                    STDDEV(gr.grade) as grade_stddev,
                    MIN(gr.grade) as min_grade,
                    MAX(gr.grade) as max_grade
                FROM `lms_analytics.enrollments` e
                JOIN `lms_analytics.groups` g ON e.group_id = g.id
                LEFT JOIN `lms_analytics.grades` gr ON e.id = gr.enrollment_id
                LEFT JOIN `lms_analytics.exams` ex ON gr.exam_id = ex.id
                {$joinConditions}
                WHERE 1=1 {$whereConditions}
                GROUP BY e.id, e.user_id, e.group_id, g.name
            )
            
            SELECT 
                'module_completion' as metric_type,
                TO_JSON_STRING(ARRAY_AGG(STRUCT(
                    enrollment_id,
                    user_id,
                    group_id,
                    group_name,
                    module_id,
                    module_title,
                    total_sessions,
                    attended_sessions,
                    SAFE_DIVIDE(attended_sessions, total_sessions) * 100 as completion_rate,
                    completion_days
                ))) as data
            FROM module_completion
            WHERE total_sessions > 0
            
            UNION ALL
            
            SELECT 
                'grade_consistency' as metric_type,
                TO_JSON_STRING(ARRAY_AGG(STRUCT(
                    enrollment_id,
                    user_id,
                    group_id,
                    group_name,
                    total_grades,
                    avg_grade,
                    grade_stddev,
                    min_grade,
                    max_grade
                ))) as data
            FROM grade_consistency
            WHERE total_grades > 0
        ";

        $cacheKey = 'progress_metrics_' . md5(serialize($filters));
        
        try {
            $results = $this->executeCachedQuery($query, $cacheKey);
            return $this->formatProgressResults($results);
        } catch (\Exception $e) {
            Log::error('BigQuery Progress Error - Query: ' . $query);
            Log::error('BigQuery Progress Error - Filters: ' . json_encode($filters));
            throw $e;
        }
    }

    /**
     * JOINs específicos para métricas de progreso
     */
    private function buildProgressJoinClause(array $filters): string
    {
        $joins = [];

        if (isset($filters['course_version_id'])) {
            $joins[] = "JOIN `lms_analytics.course_versions` cv ON g.course_version_id = cv.id";
        }

        if (isset($filters['module_id'])) {
            $joins[] = "JOIN `lms_analytics.modules` m ON g.course_version_id = m.course_version_id";
        }

        return implode(' ', $joins);
    }

    /**
     * WHERE específico para métricas de progreso
     */
    private function buildProgressWhereClause(array $filters): string
    {
        $conditions = [];

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'group_id':
                    $conditions[] = "g.id = " . (int)$value;
                    break;
                case 'user_id':
                    $conditions[] = "e.user_id = " . (int)$value;
                    break;
                case 'course_version_id':
                    $conditions[] = "g.course_version_id = " . (int)$value;
                    break;
                case 'module_id':
                    $conditions[] = "m.id = " . (int)$value;
                    break;
                case 'start_date':
                    $conditions[] = "cs.start_time >= TIMESTAMP('{$value}')";
                    break;
                case 'end_date':
                    $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                    $conditions[] = "cs.start_time < TIMESTAMP('{$nextDay}')";
                    break;
            }
        }

        return $conditions ? ' AND ' . implode(' AND ', $conditions) : '';
    }

    /**
     * Métricas de rendimiento académico - CORREGIDO
     */
    public function getPerformanceMetrics(array $filters = []): array
    {
        $joinConditions = $this->buildPerformanceJoinClause($filters);
        $whereConditions = $this->buildPerformanceWhereClause($filters);

        $query = "
            WITH grade_distribution AS (
                SELECT
                    CASE 
                        WHEN gr.grade >= 90 THEN '90-100'
                        WHEN gr.grade >= 80 THEN '80-89'
                        WHEN gr.grade >= 70 THEN '70-79'
                        WHEN gr.grade >= 60 THEN '60-69'
                        ELSE '0-59'
                    END as grade_range,
                    COUNT(*) as frequency,
                    e.group_id,
                    g.name as group_name
                FROM `lms_analytics.grades` gr
                JOIN `lms_analytics.enrollments` e ON gr.enrollment_id = e.id
                JOIN `lms_analytics.groups` g ON e.group_id = g.id
                {$joinConditions}
                WHERE 1=1 {$whereConditions}
                GROUP BY grade_range, e.group_id, g.name
            ),
            instructor_performance AS (
                SELECT
                    g.id as group_id,
                    g.name as group_name,
                    COUNT(DISTINCT e.id) as total_students,
                    AVG(er.final_grade) as avg_final_grade,
                    AVG(er.attendance_percentage) as avg_attendance,
                    COUNT(DISTINCT CASE WHEN er.status = 'approved' THEN e.id END) as approved_students,
                    SAFE_DIVIDE(
                        COUNT(DISTINCT CASE WHEN er.status = 'approved' THEN e.id END), 
                        COUNT(DISTINCT e.id)
                    ) * 100 as approval_rate
                FROM `lms_analytics.groups` g
                LEFT JOIN `lms_analytics.enrollments` e ON g.id = e.group_id
                LEFT JOIN `lms_analytics.enrollment_results` er ON e.id = er.enrollment_id
                {$joinConditions}
                WHERE 1=1 {$whereConditions}
                GROUP BY g.id, g.name
            ),
            attendance_performance_correlation AS (
                SELECT
                    CORR(er.final_grade, er.attendance_percentage) as overall_correlation
                FROM `lms_analytics.enrollment_results` er
                JOIN `lms_analytics.enrollments` e ON er.enrollment_id = e.id
                JOIN `lms_analytics.groups` g ON e.group_id = g.id
                {$joinConditions}
                WHERE 1=1 {$whereConditions}
                    AND er.final_grade IS NOT NULL 
                    AND er.attendance_percentage IS NOT NULL
            )
            
            SELECT 
                'grade_distribution' as metric_type,
                TO_JSON_STRING(ARRAY_AGG(STRUCT(
                    grade_range,
                    frequency,
                    group_id,
                    group_name
                ))) as data
            FROM grade_distribution
            
            UNION ALL
            
            SELECT 
                'instructor_performance' as metric_type,
                TO_JSON_STRING(ARRAY_AGG(STRUCT(
                    group_id,
                    group_name,
                    total_students,
                    avg_final_grade,
                    avg_attendance,
                    approved_students,
                    approval_rate
                ))) as data
            FROM instructor_performance
            
            UNION ALL
            
            SELECT 
                'attendance_correlation' as metric_type,
                TO_JSON_STRING([STRUCT(
                    overall_correlation
                )]) as data
            FROM attendance_performance_correlation
        ";

        $cacheKey = 'performance_metrics_' . md5(serialize($filters));
        
        try {
            $results = $this->executeCachedQuery($query, $cacheKey);
            return $this->formatPerformanceResults($results);
        } catch (\Exception $e) {
            Log::error('BigQuery Performance Error - Query: ' . $query);
            Log::error('BigQuery Performance Error - Filters: ' . json_encode($filters));
            throw $e;
        }
    }

    /**
     * JOINs específicos para métricas de rendimiento
     */
    private function buildPerformanceJoinClause(array $filters): string
    {
        $joins = [];

        // Solo agregar JOINs adicionales si se necesitan para filtros específicos
        if (isset($filters['course_version_id'])) {
            $joins[] = "JOIN `lms_analytics.course_versions` cv ON g.course_version_id = cv.id";
        }

        if (isset($filters['start_date']) || isset($filters['end_date'])) {
            // Para filtros de fecha en grade_distribution, necesitamos exams
            $joins[] = "LEFT JOIN `lms_analytics.exams` ex ON gr.exam_id = ex.id";
        }

        return implode(' ', $joins);
    }

    /**
     * WHERE específico para métricas de rendimiento
     */
    private function buildPerformanceWhereClause(array $filters): string
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
                    // Para rendimiento, filtramos por fecha de examen o fecha del grupo
                    if (isset($filters['group_id']) || isset($filters['course_version_id'])) {
                        // Si hay filtros de grupo, usamos las fechas del grupo
                        $conditions[] = "g.start_date >= DATE('{$value}')";
                    } else {
                        // Si no, usamos fecha de examen
                        $conditions[] = "ex.start_time >= TIMESTAMP('{$value}')";
                    }
                    break;
                case 'end_date':
                    if (isset($filters['group_id']) || isset($filters['course_version_id'])) {
                        $conditions[] = "g.end_date <= DATE('{$value}')";
                    } else {
                        $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                        $conditions[] = "ex.start_time < TIMESTAMP('{$nextDay}')";
                    }
                    break;
            }
        }

        return $conditions ? ' AND ' . implode(' AND ', $conditions) : '';
    }

    /**
     * Consulta simple de asistencia por grupo
     */
    public function getSimpleAttendanceByGroup(array $filters = []): array
    {
        $whereConditions = $this->buildSimpleWhereClause($filters);

        $query = "
            SELECT
                g.name as grupo,
                COUNT(a.id) as total_registros,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as total_presentes,
                ROUND(SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.id) * 100, 2) as porcentaje_asistencia
            FROM `lms_analytics.attendances` a
            JOIN `lms_analytics.class_sessions` cs ON a.class_session_id = cs.id
            JOIN `lms_analytics.groups` g ON cs.group_id = g.id
            WHERE 1=1 {$whereConditions}
            GROUP BY grupo
            ORDER BY porcentaje_asistencia DESC
        ";

        $cacheKey = 'simple_attendance_' . md5(serialize($filters));
        return $this->executeCachedQuery($query, $cacheKey);
    }

    /**
     * WHERE para consulta simple
     */
    private function buildSimpleWhereClause(array $filters): string
    {
        $conditions = [];

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'group_id':
                    $conditions[] = "g.id = " . (int)$value;
                    break;
                case 'start_date':
                    $conditions[] = "cs.start_time >= TIMESTAMP('{$value}')";
                    break;
                case 'end_date':
                    $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                    $conditions[] = "cs.start_time < TIMESTAMP('{$nextDay}')";
                    break;
            }
        }

        return $conditions ? ' AND ' . implode(' AND ', $conditions) : '';
    }

    private function formatAttendanceResults(array $results): array
    {
        $formatted = [
            'student_level' => [],
            'group_level' => [],
            'weekly_patterns' => [],
            'summary' => [
                'total_students' => 0,
                'avg_attendance_rate' => 0,
                'total_sessions' => 0
            ]
        ];

        foreach ($results as $row) {
            $data = json_decode($row['data'], true) ?? [];
            
            switch ($row['metric_type']) {
                case 'student_level':
                    $formatted['student_level'] = $data;
                    break;
                case 'group_level':
                    $formatted['group_level'] = $data;
                    break;
                case 'weekly_patterns':
                    $formatted['weekly_patterns'] = $data;
                    break;
            }
        }

        if (!empty($formatted['student_level'])) {
            $formatted['summary']['total_students'] = count($formatted['student_level']);
            
            $attendanceRates = array_column($formatted['student_level'], 'attendance_rate');
            $validRates = array_filter($attendanceRates, function($rate) {
                return $rate !== null && is_numeric($rate);
            });
            
            if (!empty($validRates)) {
                $formatted['summary']['avg_attendance_rate'] = round(array_sum($validRates) / count($validRates), 2);
            }
            
            $formatted['summary']['total_sessions'] = array_sum(array_column($formatted['student_level'], 'total_sessions'));
        }

        return $formatted;
    }

    private function formatProgressResults(array $results): array
    {
        $formatted = [
            'module_completion' => [],
            'grade_consistency' => [],
            'summary' => [
                'avg_completion_rate' => 0,
                'avg_grade' => 0
            ]
        ];

        foreach ($results as $row) {
            $data = json_decode($row['data'], true) ?? [];
            
            switch ($row['metric_type']) {
                case 'module_completion':
                    $formatted['module_completion'] = $data;
                    break;
                case 'grade_consistency':
                    $formatted['grade_consistency'] = $data;
                    break;
            }
        }

        if (!empty($formatted['module_completion'])) {
            $completionRates = array_column($formatted['module_completion'], 'completion_rate');
            $validRates = array_filter($completionRates, function($rate) {
                return $rate !== null && is_numeric($rate);
            });
            
            if (!empty($validRates)) {
                $formatted['summary']['avg_completion_rate'] = round(array_sum($validRates) / count($validRates), 2);
            }
        }

        if (!empty($formatted['grade_consistency'])) {
            $avgGrades = array_column($formatted['grade_consistency'], 'avg_grade');
            $validGrades = array_filter($avgGrades, function($grade) {
                return $grade !== null && is_numeric($grade);
            });
            
            if (!empty($validGrades)) {
                $formatted['summary']['avg_grade'] = round(array_sum($validGrades) / count($validGrades), 2);
            }
        }

        return $formatted;
    }

    private function formatPerformanceResults(array $results): array
    {
        $formatted = [
            'grade_distribution' => [],
            'instructor_performance' => [],
            'attendance_correlation' => 0,
            'summary' => [
                'overall_approval_rate' => 0,
                'avg_final_grade' => 0,
                'attendance_correlation' => 0
            ]
        ];

        foreach ($results as $row) {
            $data = json_decode($row['data'], true) ?? [];
            
            switch ($row['metric_type']) {
                case 'grade_distribution':
                    $formatted['grade_distribution'] = $data;
                    break;
                case 'instructor_performance':
                    $formatted['instructor_performance'] = $data;
                    break;
                case 'attendance_correlation':
                    $formatted['attendance_correlation'] = $data[0]['overall_correlation'] ?? 0;
                    break;
            }
        }

        // Calcular resumen solo si hay datos
        if (!empty($formatted['instructor_performance'])) {
            $approvalRates = array_column($formatted['instructor_performance'], 'approval_rate');
            $validRates = array_filter($approvalRates, function($rate) {
                return $rate !== null && is_numeric($rate);
            });
            
            if (!empty($validRates)) {
                $formatted['summary']['overall_approval_rate'] = round(array_sum($validRates) / count($validRates), 2);
            }

            $finalGrades = array_column($formatted['instructor_performance'], 'avg_final_grade');
            $validGrades = array_filter($finalGrades, function($grade) {
                return $grade !== null && is_numeric($grade);
            });
            
            if (!empty($validGrades)) {
                $formatted['summary']['avg_final_grade'] = round(array_sum($validGrades) / count($validGrades), 2);
            }
        }

        $formatted['summary']['attendance_correlation'] = round($formatted['attendance_correlation'], 4);

        return $formatted;
    }

    /**
     * Estudiantes con matrícula activa y su rendimiento
     */
    public function getActiveStudentsMetrics(array $filters = []): array
    {
        $whereConditions = $this->buildActiveStudentsWhereClause($filters);

        $query = "
            WITH active_students AS (
                SELECT 
                    e.id as enrollment_id,
                    u.id as user_id,
                    u.name as student_name,
                    u.email as student_email,
                    g.id as group_id,
                    g.name as group_name,
                    c.name as course_name,
                    e.academic_status,
                    e.payment_status,
                    er.final_grade,
                    er.attendance_percentage,
                    er.status as enrollment_status,
                    COUNT(DISTINCT a.id) as total_attendances,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    AVG(gr.grade) as avg_grade,
                    COUNT(gr.id) as total_exams_taken
                FROM `lms_analytics.enrollments` e
                JOIN `lms_analytics.users` u ON e.user_id = u.id
                JOIN `lms_analytics.groups` g ON e.group_id = g.id
                JOIN `lms_analytics.course_versions` cv ON g.course_version_id = cv.id
                JOIN `lms_analytics.courses` c ON cv.course_id = c.id
                LEFT JOIN `lms_analytics.enrollment_results` er ON e.id = er.enrollment_id
                LEFT JOIN `lms_analytics.attendances` a ON e.id = a.enrollment_id
                LEFT JOIN `lms_analytics.grades` gr ON e.id = gr.enrollment_id
                WHERE 1=1 {$whereConditions}
                    AND e.academic_status = 'active'
                    AND g.status = 'active'
                GROUP BY 
                    e.id, u.id, u.name, u.email, g.id, g.name, c.name, 
                    e.academic_status, e.payment_status, er.final_grade, 
                    er.attendance_percentage, er.status
            ),
            performance_summary AS (
                SELECT
                    COUNT(*) as total_active_students,
                    AVG(final_grade) as avg_final_grade,
                    AVG(attendance_percentage) as avg_attendance,
                    COUNT(CASE WHEN enrollment_status = 'approved' THEN 1 END) as approved_students,
                    COUNT(CASE WHEN payment_status = 'paid' THEN 1 END) as paid_students,
                    COUNT(CASE WHEN final_grade >= 70 THEN 1 END) as students_passing
                FROM active_students
            )
            
            SELECT 
                'student_details' as metric_type,
                TO_JSON_STRING(ARRAY_AGG(STRUCT(
                    enrollment_id,
                    user_id,
                    student_name,
                    student_email,
                    group_id,
                    group_name,
                    course_name,
                    academic_status,
                    payment_status,
                    final_grade,
                    attendance_percentage,
                    enrollment_status,
                    total_attendances,
                    present_count,
                    avg_grade,
                    total_exams_taken,
                    SAFE_DIVIDE(present_count, total_attendances) * 100 as attendance_rate
                ))) as data
            FROM active_students
            
            UNION ALL
            
            SELECT 
                'performance_summary' as metric_type,
                TO_JSON_STRING([STRUCT(
                    total_active_students,
                    avg_final_grade,
                    avg_attendance,
                    approved_students,
                    paid_students,
                    students_passing,
                    SAFE_DIVIDE(students_passing, total_active_students) * 100 as passing_rate,
                    SAFE_DIVIDE(paid_students, total_active_students) * 100 as payment_rate
                )]) as data
            FROM performance_summary
        ";

        $cacheKey = 'active_students_' . md5(serialize($filters));
        $results = $this->executeCachedQuery($query, $cacheKey);

        return $this->formatActiveStudentsResults($results);
    }

    // ========== MÉTODOS AUXILIARES PARA WHERE CLAUSES ==========

    private function buildActiveStudentsWhereClause(array $filters): string
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
                case 'course_id':
                    $conditions[] = "c.id = " . (int)$value;
                    break;
                case 'payment_status':
                    $conditions[] = "e.payment_status = '" . addslashes($value) . "'";
                    break;
                case 'start_date':
                    $conditions[] = "g.start_date >= DATE('{$value}')";
                    break;
                case 'end_date':
                    $conditions[] = "g.end_date <= DATE('{$value}')";
                    break;
            }
        }

        return $conditions ? ' AND ' . implode(' AND ', $conditions) : '';
    }

    // ========== MÉTODOS DE FORMATEO PARA NUEVAS CONSULTAS ==========

    private function formatActiveStudentsResults(array $results): array
    {
        $formatted = [
            'student_details' => [],
            'performance_summary' => []
        ];

        foreach ($results as $row) {
            $data = json_decode($row['data'], true) ?? [];
            
            switch ($row['metric_type']) {
                case 'student_details':
                    $formatted['student_details'] = $data;
                    break;
                case 'performance_summary':
                    $formatted['performance_summary'] = $data[0] ?? [];
                    break;
            }
        }

        return $formatted;
    }

}