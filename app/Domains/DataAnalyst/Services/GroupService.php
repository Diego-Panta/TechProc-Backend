<?php
// app/Domains/DataAnalyst/Services/GroupService.php

namespace App\Domains\DataAnalyst\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Support\Facades\Log;

class GroupService
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
     * Obtiene lista de grupos para filtros
     */
    public function getGroupsList(array $filters = []): array
    {
        $whereConditions = $this->buildGroupsWhereClause($filters);

        $query = "
            SELECT 
                g.id as group_id,
                g.name as group_name,
                c.name as course_name,
                cv.name as course_version,
                g.start_date,
                g.end_date,
                g.status,
                COUNT(DISTINCT e.id) as student_count,
                COUNT(DISTINCT gt.user_id) as teacher_count
            FROM `lms_analytics.groups` g
            LEFT JOIN `lms_analytics.course_versions` cv ON g.course_version_id = cv.id
            LEFT JOIN `lms_analytics.courses` c ON cv.course_id = c.id
            LEFT JOIN `lms_analytics.enrollments` e ON g.id = e.group_id 
                AND e.academic_status = 'active'
                AND e.payment_status = 'paid'
            LEFT JOIN `lms_analytics.group_teachers` gt ON g.id = gt.group_id
            WHERE 1=1 {$whereConditions}
            GROUP BY g.id, g.name, c.name, cv.name, g.start_date, g.end_date, g.status
            ORDER BY g.status, g.start_date DESC, g.name
        ";

        try {
            $queryJobConfig = $this->bigQuery->query($query);
            $queryResults = $this->bigQuery->runQuery($queryJobConfig);
            
            $groups = [];
            foreach ($queryResults as $row) {
                $groups[] = [
                    'id' => $row['group_id'],
                    'name' => $row['group_name'],
                    'course_name' => $row['course_name'],
                    'course_version' => $row['course_version'],
                    'start_date' => $row['start_date'] ? $row['start_date']->formatAsString() : null,
                    'end_date' => $row['end_date'] ? $row['end_date']->formatAsString() : null,
                    'status' => $row['status'],
                    'student_count' => $row['student_count'],
                    'teacher_count' => $row['teacher_count'],
                    'display_name' => "{$row['group_name']} - {$row['course_name']} ({$row['student_count']} estudiantes)"
                ];
            }
            
            return $groups;
        } catch (\Exception $e) {
            Log::error('Error obteniendo lista de grupos: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene grupos activos para filtros (simplificado)
     */
    public function getActiveGroups(): array
    {
        return $this->getGroupsList(['status' => 'active']);
    }

    private function buildGroupsWhereClause(array $filters): string
    {
        $conditions = [];

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'status':
                    $conditions[] = "g.status = '{$value}'";
                    break;
                case 'course_version_id':
                    $conditions[] = "g.course_version_id = " . (int)$value;
                    break;
                case 'active_only':
                    if ($value) {
                        $conditions[] = "g.status = 'active'";
                    }
                    break;
            }
        }

        return $conditions ? ' AND ' . implode(' AND ', $conditions) : '';
    }
}