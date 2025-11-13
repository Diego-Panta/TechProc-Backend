<?php
// app/Domains/DataAnalyst/Http/Controllers/AnalyticsController.php

namespace App\Domains\DataAnalyst\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Domains\DataAnalyst\Services\BigQueryAnalyticsService;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    private BigQueryAnalyticsService $analyticsService;

    public function __construct(BigQueryAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Métricas de asistencia
     */
    public function getAttendanceMetrics(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer',
                'user_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date',
                'module_id' => 'sometimes|integer'
            ]);

            $metrics = $this->analyticsService->getAttendanceMetrics($filters);

            return response()->json([
                'success' => true,
                'data' => $metrics,
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            Log::error('Error en getAttendanceMetrics: ' . $e->getMessage(), [
                'filters' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo métricas de asistencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Métricas simples de asistencia (como tu ejemplo)
     */
    public function getSimpleAttendance(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date'
            ]);

            $results = $this->analyticsService->getSimpleAttendanceByGroup($filters);

            return response()->json([
                'success' => true,
                'data' => $results,
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            Log::error('Error en getSimpleAttendance: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo métricas simples de asistencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Métricas de progreso
     */
    public function getProgressMetrics(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'user_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date'
            ]);

            $metrics = $this->analyticsService->getProgressMetrics($filters);

            return response()->json([
                'success' => true,
                'data' => $metrics,
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            Log::error('Error en getProgressMetrics: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo métricas de progreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Métricas de rendimiento
     */
    public function getPerformanceMetrics(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date'
            ]);

            $metrics = $this->analyticsService->getPerformanceMetrics($filters);

            return response()->json([
                'success' => true,
                'data' => $metrics,
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            Log::error('Error en getPerformanceMetrics: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo métricas de rendimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estudiantes con matrícula activa
     */
    public function getActiveStudents(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer',
                'payment_status' => 'sometimes|string',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date'
            ]);

            $metrics = $this->analyticsService->getActiveStudentsMetrics($filters);

            return response()->json([
                'success' => true,
                'data' => $metrics,
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            Log::error('Error en getActiveStudents: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo métricas de estudiantes activos: ' . $e->getMessage()
            ], 500);
        }
    }
}