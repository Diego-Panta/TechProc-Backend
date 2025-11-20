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
     * Métricas de asistencia usando vista mejorada
     */
    public function getAttendanceMetrics(array $filters = []): array
    {
        $whereConditions = $this->buildWhereClause($filters, 'attendance');
        
        $query = "
            SELECT 
                metric_type,
                data
            FROM `lms_analytics.vw_attendance_metrics`
            WHERE 1=1 {$whereConditions}
        ";

        $cacheKey = 'attendance_metrics_' . md5(serialize($filters));
        
        try {
            $results = $this->executeCachedQuery($query, $cacheKey);
            return $this->formatAttendanceResults($results);
        } catch (\Exception $e) {
            Log::error('BigQuery Attendance Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Métricas de progreso usando vista mejorada
     */
    public function getProgressMetrics(array $filters = []): array
    {
        $whereConditions = $this->buildWhereClause($filters, 'progress');
        
        $query = "
            SELECT 
                metric_type,
                data
            FROM `lms_analytics.vw_progress_metrics`
            WHERE 1=1 {$whereConditions}
        ";

        $cacheKey = 'progress_metrics_' . md5(serialize($filters));
        
        try {
            $results = $this->executeCachedQuery($query, $cacheKey);
            return $this->formatProgressResults($results);
        } catch (\Exception $e) {
            Log::error('BigQuery Progress Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Métricas de rendimiento usando vista mejorada
     */
    public function getPerformanceMetrics(array $filters = []): array
    {
        $whereConditions = $this->buildWhereClause($filters, 'performance');
        
        $query = "
            SELECT 
                metric_type,
                data
            FROM `lms_analytics.vw_performance_metrics`
            WHERE 1=1 {$whereConditions}
        ";

        $cacheKey = 'performance_metrics_' . md5(serialize($filters));
        
        try {
            $results = $this->executeCachedQuery($query, $cacheKey);
            return $this->formatPerformanceResults($results);
        } catch (\Exception $e) {
            Log::error('BigQuery Performance Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function buildWhereClause(array $filters, string $metricType): string
    {
        $conditions = [];

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'group_id':
                    $conditions[] = "EXISTS (SELECT 1 FROM UNNEST(data) d WHERE d.group_id = '" . (int)$value . "')";
                    break;
                case 'user_id':
                    $conditions[] = "EXISTS (SELECT 1 FROM UNNEST(data) d WHERE d.user_id = '" . (int)$value . "')";
                    break;
                case 'course_version_id':
                    $conditions[] = "EXISTS (SELECT 1 FROM UNNEST(data) d JOIN `lms_analytics.groups` g ON CAST(d.group_id AS INT64) = g.id WHERE g.course_version_id = " . (int)$value . ")";
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
            'summary' => [
                'total_students' => 0,
                'avg_attendance_rate' => 0,
                'total_sessions' => 0
            ]
        ];

        foreach ($results as $row) {
            // Manejar el campo data que puede ser array o string JSON
            $data = $this->extractDataFromRow($row);
            
            switch ($row['metric_type']) {
                case 'student_level':
                    $formatted['student_level'] = $this->parseAttendanceStudentData($data);
                    break;
                case 'group_level':
                    $formatted['group_level'] = $this->parseAttendanceGroupData($data);
                    break;
            }
        }

        // Calcular resumen
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

    private function parseAttendanceStudentData(array $data): array
    {
        $parsed = [];
        foreach ($data as $item) {
            // Verificar si el item es un array antes de acceder a sus propiedades
            if (!is_array($item)) {
                continue;
            }
            
            $parsed[] = [
                'enrollment_id' => isset($item['id']) ? (int)$item['id'] : 0,
                'user_id' => isset($item['user_id']) ? (int)$item['user_id'] : 0,
                'student_name' => $item['student_name'] ?? '',
                'student_email' => $item['student_email'] ?? '',
                'group_id' => isset($item['group_id']) ? (int)$item['group_id'] : 0,
                'group_name' => $item['group_name'] ?? '',
                'course_name' => $item['course_name'] ?? '',
                'course_version' => $item['course_version'] ?? '',
                'total_sessions' => isset($item['total_sessions']) ? (int)$item['total_sessions'] : 0,
                'present_count' => isset($item['present_count']) ? (int)$item['present_count'] : 0,
                'absent_count' => isset($item['absent_count']) ? (int)$item['absent_count'] : 0,
                'late_count' => isset($item['late_count']) ? (int)$item['late_count'] : 0,
                'attendance_rate' => isset($item['attendance_rate']) ? (float)$item['attendance_rate'] : 0.0
            ];
        }
        return $parsed;
    }

    private function parseAttendanceGroupData(array $data): array
    {
        $parsed = [];
        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }
            
            $parsed[] = [
                'group_id' => isset($item['id']) ? (int)$item['id'] : 0,
                'group_name' => $item['group_name'] ?? '',
                'course_name' => $item['course_name'] ?? '',
                'course_version' => $item['course_version'] ?? '',
                'total_students' => isset($item['total_students']) ? (int)$item['total_students'] : 0,
                'avg_attendance_rate' => isset($item['avg_attendance_rate']) ? (float)$item['avg_attendance_rate'] : 0.0,
                'avg_absence_rate' => isset($item['avg_absence_rate']) ? (float)$item['avg_absence_rate'] : 0.0
            ];
        }
        return $parsed;
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
            $data = $this->extractDataFromRow($row);
            
            switch ($row['metric_type']) {
                case 'module_completion':
                    $formatted['module_completion'] = $this->parseProgressModuleData($data);
                    break;
                case 'grade_consistency':
                    $formatted['grade_consistency'] = $this->parseProgressGradeData($data);
                    break;
            }
        }

        // Calcular resumen
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

    private function parseProgressModuleData(array $data): array
    {
        $parsed = [];
        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }
            
            $parsed[] = [
                'enrollment_id' => isset($item['id']) ? (int)$item['id'] : 0,
                'user_id' => isset($item['user_id']) ? (int)$item['user_id'] : 0,
                'student_name' => $item['student_name'] ?? '',
                'student_email' => $item['student_email'] ?? '',
                'group_id' => isset($item['group_id']) ? (int)$item['group_id'] : 0,
                'group_name' => $item['group_name'] ?? '',
                'course_name' => $item['course_name'] ?? '',
                'course_version' => $item['course_version'] ?? '',
                'module_id' => isset($item['module_id']) ? (int)$item['module_id'] : 0,
                'module_title' => $item['module_title'] ?? '',
                'module_order' => isset($item['module_order']) ? (int)$item['module_order'] : 0,
                'total_sessions' => isset($item['total_sessions']) ? (int)$item['total_sessions'] : 0,
                'attended_sessions' => isset($item['attended_sessions']) ? (int)$item['attended_sessions'] : 0,
                'completion_rate' => isset($item['completion_rate']) ? (float)$item['completion_rate'] : 0.0,
                'completion_days' => isset($item['completion_days']) ? (int)$item['completion_days'] : 0
            ];
        }
        return $parsed;
    }

    private function parseProgressGradeData(array $data): array
    {
        $parsed = [];
        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }
            
            $parsed[] = [
                'enrollment_id' => isset($item['id']) ? (int)$item['id'] : 0,
                'user_id' => isset($item['user_id']) ? (int)$item['user_id'] : 0,
                'student_name' => $item['student_name'] ?? '',
                'student_email' => $item['student_email'] ?? '',
                'group_id' => isset($item['group_id']) ? (int)$item['group_id'] : 0,
                'group_name' => $item['group_name'] ?? '',
                'course_name' => $item['course_name'] ?? '',
                'course_version' => $item['course_version'] ?? '',
                'total_grades' => isset($item['total_grades']) ? (int)$item['total_grades'] : 0,
                'avg_grade' => isset($item['avg_grade']) ? (float)$item['avg_grade'] : 0.0,
                'grade_stddev' => isset($item['grade_stddev']) ? (float)$item['grade_stddev'] : 0.0,
                'min_grade' => isset($item['min_grade']) ? (float)$item['min_grade'] : 0.0,
                'max_grade' => isset($item['max_grade']) ? (float)$item['max_grade'] : 0.0
            ];
        }
        return $parsed;
    }

    private function formatPerformanceResults(array $results): array
    {
        $formatted = [
            'student_performance' => [],
            'course_performance' => [],
            'summary' => [
                'total_students' => 0,
                'total_courses' => 0,
                'overall_approval_rate' => 0,
                'overall_avg_grade' => 0
            ]
        ];

        foreach ($results as $row) {
            $data = $this->extractDataFromRow($row);
            
            switch ($row['metric_type']) {
                case 'student_performance':
                    $formatted['student_performance'] = $this->parseStudentPerformanceData($data);
                    break;
                case 'course_performance':
                    $formatted['course_performance'] = $this->parseCoursePerformanceData($data);
                    break;
            }
        }

        // Calcular resumen
        $formatted['summary']['total_students'] = count($formatted['student_performance']);
        $formatted['summary']['total_courses'] = count(array_unique(array_column($formatted['course_performance'], 'course_name')));

        if (!empty($formatted['course_performance'])) {
            $approvalRates = array_column($formatted['course_performance'], 'approval_rate');
            $validRates = array_filter($approvalRates, function($rate) {
                return $rate !== null && is_numeric($rate);
            });
            
            if (!empty($validRates)) {
                $formatted['summary']['overall_approval_rate'] = round(array_sum($validRates) / count($validRates), 2);
            }

            $finalGrades = array_column($formatted['course_performance'], 'avg_final_grade');
            $validGrades = array_filter($finalGrades, function($grade) {
                return $grade !== null && is_numeric($grade);
            });
            
            if (!empty($validGrades)) {
                $formatted['summary']['overall_avg_grade'] = round(array_sum($validGrades) / count($validGrades), 2);
            }
        }

        return $formatted;
    }

    private function parseStudentPerformanceData(array $data): array
    {
        $parsed = [];
        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }
            
            $parsed[] = [
                'enrollment_id' => isset($item['id']) ? (int)$item['id'] : 0,
                'user_id' => isset($item['user_id']) ? (int)$item['user_id'] : 0,
                'student_name' => $item['student_name'] ?? '',
                'student_email' => $item['student_email'] ?? '',
                'group_id' => isset($item['group_id']) ? (int)$item['group_id'] : 0,
                'group_name' => $item['group_name'] ?? '',
                'course_name' => $item['course_name'] ?? '',
                'course_version' => $item['course_version'] ?? '',
                'final_grade' => isset($item['final_grade']) ? (float)$item['final_grade'] : null,
                'attendance_percentage' => isset($item['attendance_percentage']) ? (float)$item['attendance_percentage'] : null,
                'enrollment_status' => $item['enrollment_status'] ?? '',
                'total_exams_taken' => isset($item['total_exams_taken']) ? (int)$item['total_exams_taken'] : 0,
                'overall_avg_grade' => isset($item['overall_avg_grade']) ? (float)$item['overall_avg_grade'] : null,
                'min_grade' => isset($item['min_grade']) ? (float)$item['min_grade'] : null,
                'max_grade' => isset($item['max_grade']) ? (float)$item['max_grade'] : null,
                'grade_stddev' => isset($item['grade_stddev']) ? (float)$item['grade_stddev'] : null
            ];
        }
        return $parsed;
    }

    private function parseCoursePerformanceData(array $data): array
    {
        $parsed = [];
        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }
            
            $parsed[] = [
                'group_id' => isset($item['id']) ? (int)$item['id'] : 0,
                'group_name' => $item['group_name'] ?? '',
                'course_name' => $item['course_name'] ?? '',
                'course_version' => $item['course_version'] ?? '',
                'total_students' => isset($item['total_students']) ? (int)$item['total_students'] : 0,
                'avg_final_grade' => isset($item['avg_final_grade']) ? (float)$item['avg_final_grade'] : null,
                'avg_attendance' => isset($item['avg_attendance']) ? (float)$item['avg_attendance'] : null,
                'approved_students' => isset($item['approved_students']) ? (int)$item['approved_students'] : 0,
                'approval_rate' => isset($item['approval_rate']) ? (float)$item['approval_rate'] : 0.0
            ];
        }
        return $parsed;
    }

    /**
     * Extrae datos de la fila manejando diferentes formatos
     */
    private function extractDataFromRow(array $row): array
    {
        $data = $row['data'] ?? [];
        
        // Si data es un string JSON, decodificarlo
        if (is_string($data)) {
            return json_decode($data, true) ?? [];
        }
        
        // Si data es ya un array, devolverlo directamente
        if (is_array($data)) {
            return $data;
        }
        
        // Si no es ninguno de los anteriores, devolver array vacío
        return [];
    }
}