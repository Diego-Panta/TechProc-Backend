<?php
// app/Domains/DataAnalyst/Services/AttendanceDataService.php

namespace App\Domains\DataAnalyst\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Support\Facades\Log;

class AttendanceDataService
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
     * Métricas de asistencia COMPLETAS con filtros
     */
    public function getAttendanceMetricsWithFilters(array $filters = []): array
    {
        $studentLevel = $this->getStudentLevelMetrics($filters);
        $groupLevel = $this->getGroupLevelMetrics($filters);

        return [
            'student_level' => $studentLevel,
            'group_level' => $groupLevel,
            'summary' => $this->calculateAttendanceSummary($studentLevel, $groupLevel),
            'filters_applied' => $this->getAttendanceFiltersSummary($filters),
            'data_range_info' => $this->getDataRangeInfo($filters),
        ];
    }

    /**
     * Métricas a nivel de estudiante
     */
    private function getStudentLevelMetrics(array $filters): array
    {
        $whereConditions = $this->buildAttendanceWhereClause($filters);

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
                COUNT(a.id) as total_sessions,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
                CASE 
                    WHEN COUNT(a.id) > 0 THEN
                        ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(a.id)), 2)
                    ELSE 0
                END as attendance_rate
            FROM `lms_analytics.enrollments` e
            JOIN `lms_analytics.groups` g ON e.group_id = g.id
            JOIN `lms_analytics.attendances` a ON e.id = a.enrollment_id
            JOIN `lms_analytics.class_sessions` cs ON a.class_session_id = cs.id
            LEFT JOIN `lms_analytics.users` u ON e.user_id = u.id
            LEFT JOIN `lms_analytics.course_versions` cv ON g.course_version_id = cv.id
            LEFT JOIN `lms_analytics.courses` c ON cv.course_id = c.id
            WHERE g.status = 'active' 
              AND e.academic_status = 'active'
              AND e.payment_status = 'paid'
              {$whereConditions}
            GROUP BY e.id, e.user_id, u.name, u.email, e.group_id, g.name, c.name, cv.name
            HAVING total_sessions > 0
            ORDER BY student_name
        ";

        try {
            Log::info("Attendance Query Executed", [
                'query' => $query,
                'filters' => $filters,
                'where_conditions' => $whereConditions
            ]);

            $results = $this->executeQuery($query);
            return $this->formatStudentLevelResults($results);
        } catch (\Exception $e) {
            Log::error('Error en getStudentLevelMetrics: ' . $e->getMessage());
            return [];
        }
    }


    /**
     * Métricas a nivel de grupo
     */
    private function getGroupLevelMetrics(array $filters): array
    {
        // Primero obtener los estudiantes con sus métricas
        $studentMetrics = $this->getStudentLevelMetrics($filters);

        if (empty($studentMetrics)) {
            return [];
        }

        // Agrupar por grupo manualmente
        $groups = [];
        foreach ($studentMetrics as $student) {
            $groupId = $student['group_id'];

            if (!isset($groups[$groupId])) {
                $groups[$groupId] = [
                    'group_id' => $groupId,
                    'group_name' => $student['group_name'],
                    'course_name' => $student['course_name'],
                    'course_version' => $student['course_version'],
                    'students' => [],
                    'attendance_rates' => [],
                    'absence_rates' => []
                ];
            }

            $groups[$groupId]['students'][] = $student['student_name'];
            $groups[$groupId]['attendance_rates'][] = $student['attendance_rate'];
            $groups[$groupId]['absence_rates'][] = $student['absent_count'] > 0 ?
                ($student['absent_count'] * 100.0 / $student['total_sessions']) : 0;
        }

        // Calcular promedios por grupo
        $groupLevel = [];
        foreach ($groups as $group) {
            $groupLevel[] = [
                'group_id' => $group['group_id'],
                'group_name' => $group['group_name'],
                'course_name' => $group['course_name'],
                'course_version' => $group['course_version'],
                'total_students' => count($group['students']),
                'avg_attendance_rate' => round(array_sum($group['attendance_rates']) / count($group['attendance_rates']), 2),
                'avg_absence_rate' => round(array_sum($group['absence_rates']) / count($group['absence_rates']), 2)
            ];
        }

        return $groupLevel;
    }

    /**
     * WHERE clause para consultas de asistencia
     */
    private function buildAttendanceWhereClause(array $filters): string
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
                case 'start_date':
                    $conditions[] = "cs.start_time >= TIMESTAMP('{$value} 00:00:00')";
                    break;
                case 'end_date':
                    $conditions[] = "cs.start_time <= TIMESTAMP('{$value} 23:59:59')";
                    break;
                case 'status':
                    $conditions[] = "a.status = '{$value}'";
                    break;
                case 'module_id':
                    $conditions[] = "cs.module_id = " . (int)$value;
                    break;
            }
        }

        $whereClause = $conditions ? ' AND ' . implode(' AND ', $conditions) : '';

        Log::info("Built WHERE clause", [
            'filters' => $filters,
            'conditions' => $conditions,
            'where_clause' => $whereClause
        ]);

        return $whereClause;
    }

    /**
     * Calcula resumen de asistencia
     */
    private function calculateAttendanceSummary(array $studentLevel, array $groupLevel): array
    {
        $summary = [
            'total_students' => count($studentLevel),
            'total_groups' => count($groupLevel),
            'avg_attendance_rate' => 0,
            'total_sessions' => 0,
            'total_present' => 0,
            'total_absent' => 0,
            'total_late' => 0
        ];

        // Calcular promedios y totales
        if (!empty($studentLevel)) {
            $attendanceRates = array_column($studentLevel, 'attendance_rate');
            $validRates = array_filter($attendanceRates, function ($rate) {
                return $rate !== null && is_numeric($rate);
            });

            if (!empty($validRates)) {
                $summary['avg_attendance_rate'] = round(array_sum($validRates) / count($validRates), 2);
            }

            $summary['total_sessions'] = array_sum(array_column($studentLevel, 'total_sessions'));
            $summary['total_present'] = array_sum(array_column($studentLevel, 'present_count'));
            $summary['total_absent'] = array_sum(array_column($studentLevel, 'absent_count'));
            $summary['total_late'] = array_sum(array_column($studentLevel, 'late_count'));
        }

        return $summary;
    }

    /**
     * Formatea resultados a nivel de estudiante
     */
    private function formatStudentLevelResults(array $results): array
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
                'total_sessions' => $row['total_sessions'] ?? 0,
                'present_count' => $row['present_count'] ?? 0,
                'absent_count' => $row['absent_count'] ?? 0,
                'late_count' => $row['late_count'] ?? 0,
                'attendance_rate' => $row['attendance_rate'] ?? 0.0
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
    private function getAttendanceFiltersSummary(array $filters): array
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

        if (isset($filters['status'])) {
            $summary[] = "Estado: {$filters['status']}";
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
