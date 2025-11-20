<?php
// app/Domains/DataAnalyst/Http/Controllers/AnalyticsController.php

namespace App\Domains\DataAnalyst\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Domains\DataAnalyst\Services\BigQueryAnalyticsService;
use App\Domains\DataAnalyst\Services\ExportService;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    private BigQueryAnalyticsService $analyticsService;
    private ExportService $exportService;

    public function __construct(
        BigQueryAnalyticsService $analyticsService,
        ExportService $exportService
    ) {
        $this->analyticsService = $analyticsService;
        $this->exportService = $exportService;
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
     * Exportar métricas de asistencia
     */
    public function exportAttendance(Request $request)
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer',
                'user_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date',
                'module_id' => 'sometimes|integer',
                'format' => 'required|in:pdf,excel'
            ]);

            $format = $filters['format'];
            unset($filters['format']);

            $filePath = $this->exportService->exportAttendance($filters, $format);

            return $this->exportService->downloadFile($filePath);
        } catch (\Exception $e) {
            Log::error('Error en exportAttendance: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error exportando métricas de asistencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar métricas de progreso
     */
    public function exportProgress(Request $request)
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'user_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date',
                'format' => 'required|in:pdf,excel'
            ]);

            $format = $filters['format'];
            unset($filters['format']);

            $filePath = $this->exportService->exportProgress($filters, $format);

            return $this->exportService->downloadFile($filePath);
        } catch (\Exception $e) {
            Log::error('Error en exportProgress: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error exportando métricas de progreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar métricas de rendimiento
     */
    public function exportPerformance(Request $request)
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date',
                'format' => 'required|in:pdf,excel'
            ]);

            $format = $filters['format'];
            unset($filters['format']);

            $filePath = $this->exportService->exportPerformance($filters, $format);

            return $this->exportService->downloadFile($filePath);
        } catch (\Exception $e) {
            Log::error('Error en exportPerformance: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error exportando métricas de rendimiento: ' . $e->getMessage()
            ], 500);
        }
    }
}
