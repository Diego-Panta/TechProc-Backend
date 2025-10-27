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

    public function getStudentMetrics(DashboardRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $dashboardData = $this->dashboardService->getDashboardData($filters);

            return response()->json([
                'success' => true,
                'data' => [
                    'students' => $dashboardData['students'],
                    'by_company' => $dashboardData['student_company_distribution']
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

    public function getFinancialMetrics(DashboardRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $dashboardData = $this->dashboardService->getDashboardData($filters);

            return response()->json([
                'success' => true,
                'data' => [
                    'revenue' => $dashboardData['revenue'],
                    'revenue_sources' => $dashboardData['revenue_sources_distribution'],
                    'monthly_trend' => $dashboardData['monthly_revenue_trend']
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