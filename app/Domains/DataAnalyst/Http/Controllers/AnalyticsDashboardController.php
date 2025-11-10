<?php
// app/Domains/DataAnalyst/Http/Controllers/AnalyticsDashboardController.php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Domains\DataAnalyst\Services\RiskPredictionService;
use App\Domains\DataAnalyst\Repositories\StudentDataRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsDashboardController
{
    public function __construct(
        private RiskPredictionService $riskService,
        private StudentDataRepository $studentRepository
    ) {}

    /**
     * Obtiene datos generales para el dashboard
     */
    public function getOverview(): JsonResponse
    {
        try {
            // Estadísticas generales
            $generalStats = DB::table('enrollments as e')
                ->selectRaw('
                    COUNT(*) as total_students,
                    SUM(CASE WHEN e.academic_status = "active" THEN 1 ELSE 0 END) as active_students,
                    SUM(CASE WHEN e.academic_status = "completed" THEN 1 ELSE 0 END) as completed_students,
                    SUM(CASE WHEN e.academic_status = "dropped" THEN 1 ELSE 0 END) as dropped_students,
                    AVG(er.final_grade) as average_grade,
                    AVG(er.attendance_percentage) as average_attendance
                ')
                ->leftJoin('enrollment_results as er', 'e.id', '=', 'er.enrollment_id')
                ->first();

            // Distribución de riesgo
            $riskDistribution = DB::table('student_risk_profiles as rp')
                ->selectRaw('
                    rp.risk_level,
                    COUNT(*) as count,
                    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM student_risk_profiles)), 2) as percentage
                ')
                ->join('enrollments as e', 'rp.enrollment_id', '=', 'e.id')
                ->where('e.academic_status', 'active')
                ->groupBy('rp.risk_level')
                ->get();

            // Cursos con mayor riesgo
            $highRiskCourses = DB::table('student_risk_profiles as rp')
                ->selectRaw('
                    c.name as course_name,
                    g.name as group_name,
                    COUNT(*) as total_students,
                    SUM(CASE WHEN rp.risk_level IN ("critical", "high") THEN 1 ELSE 0 END) as high_risk_count,
                    ROUND((SUM(CASE WHEN rp.risk_level IN ("critical", "high") THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) as high_risk_percentage
                ')
                ->join('enrollments as e', 'rp.enrollment_id', '=', 'e.id')
                ->join('groups as g', 'e.group_id', '=', 'g.id')
                ->join('course_versions as cv', 'g.course_version_id', '=', 'cv.id')
                ->join('courses as c', 'cv.course_id', '=', 'c.id')
                ->where('e.academic_status', 'active')
                ->groupBy('c.id', 'c.name', 'g.name')
                ->having('high_risk_count', '>', 0)
                ->orderBy('high_risk_percentage', 'desc')
                ->limit(10)
                ->get();

            // Tendencia de riesgo últimos 30 días
            $riskTrend = DB::table('student_risk_profiles as rp')
                ->selectRaw('
                    DATE(rp.last_calculated_at) as date,
                    COUNT(*) as total_calculations,
                    AVG(rp.overall_score) as average_risk_score,
                    SUM(CASE WHEN rp.risk_level IN ("critical", "high") THEN 1 ELSE 0 END) as high_risk_count
                ')
                ->where('rp.last_calculated_at', '>=', now()->subDays(30))
                ->groupBy(DB::raw('DATE(rp.last_calculated_at)'))
                ->orderBy('date')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'general_stats' => $generalStats,
                    'risk_distribution' => $riskDistribution,
                    'high_risk_courses' => $highRiskCourses,
                    'risk_trend' => $riskTrend,
                    'last_updated' => now()->toISOString()
                ],
                'message' => 'Datos del dashboard obtenidos correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene tendencias de riesgo a lo largo del tiempo
     */
    public function getRiskTrends(): JsonResponse
    {
        try {
            $days = request()->input('days', 30);

            $trends = DB::table('student_risk_profiles as rp')
                ->selectRaw('
                    DATE(rp.created_at) as date,
                    COUNT(*) as total_students,
                    AVG(rp.overall_score) as average_risk_score,
                    SUM(CASE WHEN rp.risk_level = "critical" THEN 1 ELSE 0 END) as critical_count,
                    SUM(CASE WHEN rp.risk_level = "high" THEN 1 ELSE 0 END) as high_count,
                    SUM(CASE WHEN rp.risk_level = "medium" THEN 1 ELSE 0 END) as medium_count,
                    SUM(CASE WHEN rp.risk_level = "low" THEN 1 ELSE 0 END) as low_count
                ')
                ->where('rp.created_at', '>=', now()->subDays($days))
                ->groupBy(DB::raw('DATE(rp.created_at)'))
                ->orderBy('date')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'trends' => $trends,
                    'period_days' => $days,
                    'date_range' => [
                        'start' => now()->subDays($days)->toDateString(),
                        'end' => now()->toDateString()
                    ]
                ],
                'message' => 'Tendencias de riesgo obtenidas correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tendencias: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene análisis detallado por componentes de riesgo
     */
    public function getComponentAnalysis(): JsonResponse
    {
        try {
            $components = ['academic', 'attendance', 'financial', 'engagement', 'behavioral'];
            $analysis = [];

            foreach ($components as $component) {
                $column = $component . '_score';
                
                $stats = DB::table('student_risk_profiles as rp')
                    ->selectRaw('
                        AVG(' . $column . ') as average_score,
                        MIN(' . $column . ') as min_score,
                        MAX(' . $column . ') as max_score,
                        COUNT(CASE WHEN ' . $column . ' >= 60 THEN 1 END) as high_risk_count,
                        COUNT(CASE WHEN ' . $column . ' >= 40 AND ' . $column . ' < 60 THEN 1 END) as medium_risk_count,
                        COUNT(CASE WHEN ' . $column . ' < 40 THEN 1 END) as low_risk_count
                    ')
                    ->join('enrollments as e', 'rp.enrollment_id', '=', 'e.id')
                    ->where('e.academic_status', 'active')
                    ->first();

                $analysis[$component] = $stats;
            }

            return response()->json([
                'success' => true,
                'data' => $analysis,
                'message' => 'Análisis por componentes obtenido correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en análisis de componentes: ' . $e->getMessage()
            ], 500);
        }
    }
}