<?php

namespace App\Domains\DataAnalyst\Http\Controllers\Api;

use App\Domains\DataAnalyst\Services\DashboardService;
use App\Domains\DataAnalyst\Http\Requests\Api\DashboardRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DashboardApiController
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/data-analyst/dashboard/summary",
     *     summary="Obtener resumen completo del dashboard",
     *     tags={"DataAnalyst - Dashboard"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar datos",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar datos",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="academic_period_id",
     *         in="query",
     *         description="Filtrar por período académico",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="company_id",
     *         in="query",
     *         description="Filtrar por empresa específica",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="students", type="object",
     *                     @OA\Property(property="total", type="integer", example=235),
     *                     @OA\Property(property="active", type="integer", example=198),
     *                     @OA\Property(property="growth_rate", type="number", format="float", example=12.5)
     *                 ),
     *                 @OA\Property(property="courses", type="object",
     *                     @OA\Property(property="total", type="integer", example=45),
     *                     @OA\Property(property="active", type="integer", example=40),
     *                     @OA\Property(property="total_enrollments", type="integer", example=567)
     *                 ),
     *                 @OA\Property(property="attendance", type="object",
     *                     @OA\Property(property="average_rate", type="number", format="float", example=87.5),
     *                     @OA\Property(property="trend", type="string", example="up")
     *                 ),
     *                 @OA\Property(property="performance", type="object",
     *                     @OA\Property(property="average_grade", type="number", format="float", example=16.8),
     *                     @OA\Property(property="passing_rate", type="number", format="float", example=85.3)
     *                 ),
     *                 @OA\Property(property="revenue", type="object",
     *                     @OA\Property(property="total", type="number", format="float", example=567890.50),
     *                     @OA\Property(property="growth_rate", type="number", format="float", example=8.3)
     *                 ),
     *                 @OA\Property(property="support", type="object",
     *                     @OA\Property(property="open_tickets", type="integer", example=68),
     *                     @OA\Property(property="average_resolution_time_hours", type="number", format="float", example=8.5)
     *                 ),
     *                 @OA\Property(property="security", type="object",
     *                     @OA\Property(property="active_alerts", type="integer", example=5),
     *                     @OA\Property(property="blocked_ips", type="integer", example=8)
     *                 ),
     *                 @OA\Property(property="recent_activities", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="type", type="string", example="enrollment"),
     *                         @OA\Property(property="description", type="string", example="15 nuevas matrículas hoy"),
     *                         @OA\Property(property="timestamp", type="string", format="date-time")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function getSummary(DashboardRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $dashboardData = $this->dashboardService->getDashboardData($filters);

            return response()->json([
                'success' => true,
                'data' => $dashboardData
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting dashboard summary', [
                'filters' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos del dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/dashboard/metrics/students",
     *     summary="Obtener métricas específicas de estudiantes",
     *     tags={"DataAnalyst - Dashboard"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar datos",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar datos",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="company_id",
     *         in="query",
     *         description="Filtrar por empresa específica",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Métricas de estudiantes obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total", type="integer", example=235),
     *                 @OA\Property(property="active", type="integer", example=198),
     *                 @OA\Property(property="growth_rate", type="number", format="float", example=12.5),
     *                 @OA\Property(property="by_company", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="company_id", type="integer", example=1),
     *                         @OA\Property(property="company_name", type="string", example="Tech Solutions SAC"),
     *                         @OA\Property(property="student_count", type="integer", example=45)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getStudentMetrics(DashboardRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $dashboardData = $this->dashboardService->getDashboardData($filters);

            return response()->json([
                'success' => true,
                'data' => [
                    'students' => $dashboardData['students'],
                    'by_company' => $this->getStudentCompanyDistribution($filters)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting student metrics', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las métricas de estudiantes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/dashboard/metrics/financial",
     *     summary="Obtener métricas financieras",
     *     tags={"DataAnalyst - Dashboard"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar datos",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar datos",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Métricas financieras obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="revenue", type="object",
     *                     @OA\Property(property="total", type="number", format="float", example=567890.50),
     *                     @OA\Property(property="growth_rate", type="number", format="float", example=8.3)
     *                 ),
     *                 @OA\Property(property="revenue_sources", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="source_id", type="integer", example=1),
     *                         @OA\Property(property="source_name", type="string", example="Matrículas"),
     *                         @OA\Property(property="amount", type="number", format="float", example=450000.00),
     *                         @OA\Property(property="percentage", type="number", format="float", example=79.2)
     *                     )
     *                 ),
     *                 @OA\Property(property="monthly_trend", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="month", type="string", example="2025-01"),
     *                         @OA\Property(property="revenue", type="number", format="float", example=125000.00)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getFinancialMetrics(DashboardRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $dashboardData = $this->dashboardService->getDashboardData($filters);

            return response()->json([
                'success' => true,
                'data' => [
                    'revenue' => $dashboardData['revenue'],
                    'revenue_sources' => $this->getRevenueSourcesDistribution($filters),
                    'monthly_trend' => $this->getMonthlyRevenueTrend($filters)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting financial metrics', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las métricas financieras',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/dashboard/activities/recent",
     *     summary="Obtener actividades recientes",
     *     tags={"DataAnalyst - Dashboard"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Límite de actividades a retornar",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=20, default=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Actividades recientes obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="type", type="string", example="enrollment"),
     *                     @OA\Property(property="description", type="string", example="15 nuevas matrículas hoy"),
     *                     @OA\Property(property="timestamp", type="string", format="date-time"),
     *                     @OA\Property(property="icon", type="string", example="users"),
     *                     @OA\Property(property="color", type="string", example="success")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getRecentActivities(DashboardRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $limit = $request->get('limit', 5);
            $dashboardData = $this->dashboardService->getDashboardData($filters);

            $activities = array_slice($dashboardData['recent_activities'], 0, $limit);

            // Enriquecer actividades con iconos y colores
            $enrichedActivities = array_map(function($activity) {
                $activity['icon'] = $this->getActivityIcon($activity['type']);
                $activity['color'] = $this->getActivityColor($activity['type']);
                return $activity;
            }, $activities);

            return response()->json([
                'success' => true,
                'data' => $enrichedActivities
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting recent activities', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las actividades recientes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Métodos auxiliares para datos adicionales
     */
    private function getStudentCompanyDistribution(array $filters)
    {
        // Implementar distribución por empresa
        return [
            [
                'company_id' => 1,
                'company_name' => 'Tech Solutions SAC',
                'student_count' => 45
            ],
            [
                'company_id' => 2,
                'company_name' => 'Innovation Labs',
                'student_count' => 32
            ]
        ];
    }

    private function getRevenueSourcesDistribution(array $filters)
    {
        // Implementar distribución por fuentes de ingresos
        return [
            [
                'source_id' => 1,
                'source_name' => 'Matrículas',
                'amount' => 450000.00,
                'percentage' => 79.2
            ],
            [
                'source_id' => 2,
                'source_name' => 'Certificaciones',
                'amount' => 85000.00,
                'percentage' => 15.0
            ]
        ];
    }

    private function getMonthlyRevenueTrend(array $filters)
    {
        // Implementar tendencia mensual
        return [
            ['month' => '2025-01', 'revenue' => 125000.00],
            ['month' => '2025-02', 'revenue' => 142000.00],
            ['month' => '2025-03', 'revenue' => 138000.00]
        ];
    }

    private function getActivityIcon(string $type): string
    {
        return match($type) {
            'enrollment' => 'users',
            'ticket' => 'help-circle',
            'payment' => 'credit-card',
            'student' => 'user-plus',
            'security' => 'shield',
            default => 'activity'
        };
    }

    private function getActivityColor(string $type): string
    {
        return match($type) {
            'enrollment' => 'success',
            'ticket' => 'warning',
            'payment' => 'info',
            'student' => 'primary',
            'security' => 'danger',
            default => 'secondary'
        };
    }
}