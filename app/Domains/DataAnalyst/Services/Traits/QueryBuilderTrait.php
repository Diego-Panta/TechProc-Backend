<?php
// app/Domains/DataAnalyst/Services/Traits/QueryBuilderTrait.php

namespace App\Domains\DataAnalyst\Services\Traits;

trait QueryBuilderTrait
{
    /**
     * Construye cláusulas WHERE para consultas de vistas
     */
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
                case 'only_active':
                    if ($value) {
                        $conditions[] = "EXISTS (SELECT 1 FROM UNNEST(data) d JOIN `lms_analytics.groups` g ON CAST(d.group_id AS INT64) = g.id WHERE g.status = 'active')";
                    }
                    break;
            }
        }

        return $conditions ? ' AND ' . implode(' AND ', $conditions) : '';
    }

    /**
     * WHERE clause específico para consultas de asistencia
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
     * WHERE clause específico para distribución de calificaciones
     */
    private function buildGradeDistributionWhereClause(array $filters): string
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
                    $conditions[] = "EXISTS (
                        SELECT 1 FROM `lms_analytics.exams` ex 
                        WHERE ex.id = gr.exam_id 
                        AND ex.start_time >= TIMESTAMP('{$value}')
                    )";
                    break;
                case 'end_date':
                    $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                    $conditions[] = "EXISTS (
                        SELECT 1 FROM `lms_analytics.exams` ex 
                        WHERE ex.id = gr.exam_id 
                        AND ex.start_time < TIMESTAMP('{$nextDay}')
                    )";
                    break;
            }
        }

        return $conditions ? ' AND ' . implode(' AND ', $conditions) : '';
    }

}