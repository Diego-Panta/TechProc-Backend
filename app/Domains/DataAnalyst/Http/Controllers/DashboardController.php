<?php
// app/Domains/DataAnalyst/Http/Controllers/DashboardController.php

namespace App\Domains\DataAnalyst\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Google\Cloud\BigQuery\BigQueryClient;

class DashboardController extends Controller
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
     * Dashboard simple con consultas directas
     */
    public function getDashboard(Request $request): JsonResponse
    {
        try {
            $groupId = $request->get('group_id');

            // Consulta 1: Métricas básicas de asistencia
            $attendanceQuery = "
                SELECT 
                    COUNT(DISTINCT e.id) as total_students,
                    COUNT(a.id) as total_sessions,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    ROUND(AVG(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100, 2) as attendance_rate
                FROM `lms_analytics.enrollments` e
                JOIN `lms_analytics.groups` g ON e.group_id = g.id
                JOIN `lms_analytics.attendances` a ON e.id = a.enrollment_id
                WHERE g.status = 'active' 
                  AND e.academic_status = 'active'
                  " . ($groupId ? " AND g.id = " . (int)$groupId : "") . "
            ";

            // Consulta 2: Métricas básicas de rendimiento - CORREGIDA
            $performanceQuery = "
                SELECT 
                    COUNT(DISTINCT e.id) as total_students_with_grades,
                    ROUND(AVG(gr.grade), 2) as avg_grade,
                    ROUND(
                        (COUNT(DISTINCT CASE WHEN gr.grade >= 11 THEN e.id END) * 100.0) / 
                        COUNT(DISTINCT e.id), 
                    2) as approval_rate
                FROM `lms_analytics.enrollments` e
                JOIN `lms_analytics.groups` g ON e.group_id = g.id
                JOIN `lms_analytics.grades` gr ON e.id = gr.enrollment_id
                WHERE g.status = 'active' 
                  AND e.academic_status = 'active'
                  " . ($groupId ? " AND g.id = " . (int)$groupId : "") . "
            ";

            // Consulta 3: Total de grupos activos
            $groupsQuery = "
                SELECT COUNT(*) as total_groups
                FROM `lms_analytics.groups` 
                WHERE status = 'active'
            ";

            // Ejecutar consultas
            $attendance = $this->executeSingleQuery($attendanceQuery);
            $performance = $this->executeSingleQuery($performanceQuery);
            $groups = $this->executeSingleQuery($groupsQuery);

            return response()->json([
                'success' => true,
                'data' => [
                    'attendance_rate' => $attendance['attendance_rate'] ?? 0,
                    'approval_rate' => $performance['approval_rate'] ?? 0,
                    'avg_grade' => $performance['avg_grade'] ?? 0,
                    'total_students' => $attendance['total_students'] ?? 0,
                    'total_groups' => $groups['total_groups'] ?? 0,
                    'total_sessions' => $attendance['total_sessions'] ?? 0,
                    'present_sessions' => $attendance['present_count'] ?? 0
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error en dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gráficas simples - CORREGIDAS
     */
    public function getCharts(Request $request): JsonResponse
    {
        try {
            $groupId = $request->get('group_id');

            // Gráfica 1: Distribución de asistencia - CORREGIDA
            $attendanceChartQuery = "
                SELECT 
                    a.status as attendance_status,
                    COUNT(*) as count
                FROM `lms_analytics.attendances` a
                JOIN `lms_analytics.enrollments` e ON a.enrollment_id = e.id
                JOIN `lms_analytics.groups` g ON e.group_id = g.id
                WHERE g.status = 'active'
                  " . ($groupId ? " AND g.id = " . (int)$groupId : "") . "
                GROUP BY a.status
            ";

            // Gráfica 2: Distribución de calificaciones
            $gradesChartQuery = "
                SELECT 
                    CASE 
                        WHEN grade >= 18 THEN '18-20'
                        WHEN grade >= 15 THEN '15-17'
                        WHEN grade >= 11 THEN '11-14'
                        ELSE '0-10'
                    END as grade_range,
                    COUNT(*) as count
                FROM `lms_analytics.grades` gr
                JOIN `lms_analytics.enrollments` e ON gr.enrollment_id = e.id
                JOIN `lms_analytics.groups` g ON e.group_id = g.id
                WHERE g.status = 'active'
                  " . ($groupId ? " AND g.id = " . (int)$groupId : "") . "
                GROUP BY grade_range
                ORDER BY grade_range
            ";

            $attendanceData = $this->executeQuery($attendanceChartQuery);
            $gradesData = $this->executeQuery($gradesChartQuery);

            return response()->json([
                'success' => true,
                'data' => [
                    'attendance_distribution' => $attendanceData,
                    'grade_distribution' => $gradesData
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error en gráficas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ejecuta consulta y devuelve solo el primer resultado
     */
    private function executeSingleQuery(string $query): array
    {
        $results = $this->executeQuery($query);
        return $results[0] ?? [];
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
            $results[] = iterator_to_array($row);
        }

        return $results;
    }
}