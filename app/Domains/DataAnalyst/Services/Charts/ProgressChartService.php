<?php
// app/Domains/DataAnalyst/Services/Charts/ProgressChartService.php

namespace App\Domains\DataAnalyst\Services\Charts;

use App\Domains\DataAnalyst\Services\Traits\QueryBuilderTrait;
use App\Domains\DataAnalyst\Services\Traits\CacheManagerTrait;
use App\Domains\DataAnalyst\Services\Traits\DataFormatterTrait;
use Illuminate\Support\Facades\Log;

class ProgressChartService
{
    use QueryBuilderTrait, CacheManagerTrait, DataFormatterTrait;

    protected $bigQuery;

    public function __construct($bigQuery)
    {
        $this->bigQuery = $bigQuery;
    }

    /**
     * Evolución de Calificaciones (Gráfico de Líneas)
     */
    public function getGradeEvolution(array $filters = []): array
    {
        $whereConditions = $this->buildGradeEvolutionWhereClause($filters);

        $query = "
            SELECT 
                u.name as student_name,
                g.name as group_name,
                ex.title as exam_title,
                CAST(ex.start_time AS DATE) as exam_date,
                gr.grade,
                m.title as module_title
            FROM `lms_analytics.grades` gr
            JOIN `lms_analytics.exams` ex ON gr.exam_id = ex.id
            JOIN `lms_analytics.enrollments` e ON gr.enrollment_id = e.id
            JOIN `lms_analytics.users` u ON e.user_id = u.id
            JOIN `lms_analytics.groups` g ON e.group_id = g.id
            LEFT JOIN `lms_analytics.modules` m ON ex.module_id = m.id
            WHERE gr.grade IS NOT NULL {$whereConditions}
            ORDER BY ex.start_time, u.name
        ";

        $cacheKey = $this->generateCacheKey('grade_evolution', $filters);
        
        try {
            $results = $this->executeCachedQuery($query, $cacheKey);
            
            $formattedResults = array_map(function($item) {
                if (isset($item['exam_date'])) {
                    $item['exam_date'] = $this->formatBigQueryDate($item['exam_date']);
                }
                return $item;
            }, $results);
            
            return ['grade_evolution' => $formattedResults];
        } catch (\Exception $e) {
            Log::error('Error en getGradeEvolution: ' . $e->getMessage());
            return ['grade_evolution' => []];
        }
    }

    /**
     * WHERE clause específico para evolución de calificaciones
     */
    private function buildGradeEvolutionWhereClause(array $filters): string
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
                    $conditions[] = "ex.start_time >= TIMESTAMP('{$value}')";
                    break;
                case 'end_date':
                    $nextDay = date('Y-m-d', strtotime($value . ' +1 day'));
                    $conditions[] = "ex.start_time < TIMESTAMP('{$nextDay}')";
                    break;
            }
        }

        return $conditions ? ' AND ' . implode(' AND ', $conditions) : '';
    }
}