<?php
// app/Domains/DataAnalyst/Services/Charts/AttendanceChartService.php

namespace App\Domains\DataAnalyst\Services\Charts;

use App\Domains\DataAnalyst\Services\Traits\QueryBuilderTrait;
use App\Domains\DataAnalyst\Services\Traits\CacheManagerTrait;
use App\Domains\DataAnalyst\Services\Traits\DataFormatterTrait;
use Illuminate\Support\Facades\Log;

class AttendanceChartService
{
    use QueryBuilderTrait, CacheManagerTrait, DataFormatterTrait;

    protected $bigQuery;

    public function __construct($bigQuery)
    {
        $this->bigQuery = $bigQuery;
    }

    /**
     * Distribución de Estados de Asistencia CON FILTROS
     */
    public function getAttendanceStatusDistribution(array $filters = []): array
    {
        $whereConditions = $this->buildAttendanceChartWhereClause($filters);

        $query = "
            SELECT 
                a.status,
                g.name as group_name,
                c.name as course_name,
                COUNT(*) as count,
                ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER(), 2) as percentage
            FROM `lms_analytics.attendances` a
            JOIN `lms_analytics.class_sessions` cs ON a.class_session_id = cs.id
            JOIN `lms_analytics.enrollments` e ON a.enrollment_id = e.id
            JOIN `lms_analytics.groups` g ON e.group_id = g.id
            JOIN `lms_analytics.course_versions` cv ON g.course_version_id = cv.id
            JOIN `lms_analytics.courses` c ON cv.course_id = c.id
            WHERE g.status = 'active' 
              AND e.academic_status = 'active'
              {$whereConditions}
            GROUP BY a.status, g.name, c.name
            ORDER BY g.name, a.status, count DESC
        ";

        $cacheKey = $this->generateCacheKey('attendance_status_dist', $filters);

        try {
            $results = $this->executeCachedQuery($query, $cacheKey);

            $groupedResults = [];
            foreach ($results as $row) {
                $groupKey = $row['group_name'] . ' - ' . $row['course_name'];
                if (!isset($groupedResults[$groupKey])) {
                    $groupedResults[$groupKey] = [
                        'group_name' => $row['group_name'],
                        'course_name' => $row['course_name'],
                        'statuses' => []
                    ];
                }
                $groupedResults[$groupKey]['statuses'][] = [
                    'status' => $row['status'],
                    'count' => $row['count'],
                    'percentage' => $row['percentage']
                ];
            }

            return [
                'status_distribution' => array_values($groupedResults),
                'summary' => [
                    'total_records' => array_sum(array_column($results, 'count')),
                    'total_groups' => count($groupedResults)
                ],
                'filters_applied' => $this->getChartFiltersSummary($filters)
            ];
        } catch (\Exception $e) {
            Log::error('Error en getAttendanceStatusDistribution: ' . $e->getMessage());
            return [
                'status_distribution' => [],
                'summary' => ['total_records' => 0, 'total_groups' => 0],
                'filters_applied' => []
            ];
        }
    }

    /**
     * Tendencia Semanal de Ausencias CON FILTROS
     */
    public function getWeeklyAbsenceTrends(array $filters = []): array
    {
        $whereConditions = $this->buildAttendanceChartWhereClause($filters);

        $query = "
            SELECT 
                EXTRACT(YEAR FROM cs.start_time) as year,
                EXTRACT(WEEK FROM cs.start_time) as week_number,
                CONCAT(CAST(EXTRACT(YEAR FROM cs.start_time) AS STRING), '-W', 
                       LPAD(CAST(EXTRACT(WEEK FROM cs.start_time) AS STRING), 2, '0')) as week_label,
                COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absence_count,
                COUNT(a.id) as total_attendance_records,
                COUNT(DISTINCT cs.id) as unique_sessions,
                COUNT(DISTINCT e.id) as total_students,
                ROUND(
                    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) * 100.0 / 
                    COUNT(a.id), 2
                ) as absence_rate
            FROM `lms_analytics.attendances` a
            JOIN `lms_analytics.class_sessions` cs ON a.class_session_id = cs.id
            JOIN `lms_analytics.enrollments` e ON a.enrollment_id = e.id
            JOIN `lms_analytics.groups` g ON e.group_id = g.id
            WHERE g.status = 'active' 
              AND e.academic_status = 'active'
              {$whereConditions}
            GROUP BY year, week_number, week_label
            ORDER BY year, week_number
        ";

        $cacheKey = $this->generateCacheKey('weekly_absence_trends', $filters);

        try {
            $results = $this->executeCachedQuery($query, $cacheKey);
            return [
                'weekly_trends' => $results,
                'filters_applied' => $this->getChartFiltersSummary($filters)
            ];
        } catch (\Exception $e) {
            Log::error('Error en getWeeklyAbsenceTrends: ' . $e->getMessage());
            return [
                'weekly_trends' => [],
                'filters_applied' => []
            ];
        }
    }

    /**
     * Calendario de Asistencia (Heatmap) CON FILTROS
     */
    public function getAttendanceCalendar(array $filters = []): array
    {
        $whereConditions = $this->buildAttendanceChartWhereClause($filters);

        $query = "
            SELECT 
                u.name as student_name,
                DATE(cs.start_time) as fecha,
                a.status,
                g.name as group_name,
                COUNT(*) as session_count
            FROM `lms_analytics.attendances` a
            JOIN `lms_analytics.class_sessions` cs ON a.class_session_id = cs.id
            JOIN `lms_analytics.enrollments` e ON a.enrollment_id = e.id
            JOIN `lms_analytics.users` u ON e.user_id = u.id
            JOIN `lms_analytics.groups` g ON e.group_id = g.id
            WHERE g.status = 'active' 
              AND e.academic_status = 'active'
              {$whereConditions}
            GROUP BY u.name, DATE(cs.start_time), a.status, g.name
            ORDER BY fecha, student_name
        ";

        $cacheKey = $this->generateCacheKey('attendance_calendar', $filters);

        try {
            $results = $this->executeCachedQuery($query, $cacheKey);

            $formattedResults = array_map(function($item) {
                if (isset($item['fecha'])) {
                    $item['fecha'] = $this->formatBigQueryDate($item['fecha']);
                }
                return $item;
            }, $results);

            return [
                'attendance_calendar' => $formattedResults,
                'filters_applied' => $this->getChartFiltersSummary($filters)
            ];
        } catch (\Exception $e) {
            Log::error('Error en getAttendanceCalendar: ' . $e->getMessage());
            return [
                'attendance_calendar' => [],
                'filters_applied' => []
            ];
        }
    }

    /**
     * WHERE clause para gráficas de asistencia
     */
    private function buildAttendanceChartWhereClause(array $filters): string
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
                    $conditions[] = "cs.start_time >= TIMESTAMP('{$value}')";
                    break;
                case 'end_date':
                    $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                    $conditions[] = "cs.start_time < TIMESTAMP('{$nextDay}')";
                    break;
                case 'status':
                    $conditions[] = "a.status = '{$value}'";
                    break;
            }
        }

        return $conditions ? ' AND ' . implode(' AND ', $conditions) : '';
    }

    /**
     * Genera resumen de filtros aplicados en gráficas
     */
    private function getChartFiltersSummary(array $filters): array
    {
        $summary = [];
        
        if (isset($filters['group_id'])) {
            $summary[] = "Grupo ID: {$filters['group_id']}";
        }
        
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $summary[] = "Período: {$filters['start_date']} a {$filters['end_date']}";
        } elseif (isset($filters['start_date'])) {
            $summary[] = "Desde: {$filters['start_date']}";
        } elseif (isset($filters['end_date'])) {
            $summary[] = "Hasta: {$filters['end_date']}";
        }
        
        return empty($summary) ? ['Sin filtros aplicados'] : $summary;
    }
}