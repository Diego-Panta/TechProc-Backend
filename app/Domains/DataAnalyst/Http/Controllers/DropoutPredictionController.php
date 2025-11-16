<?php
// app/Domains/DataAnalyst/Http/Controllers/DropoutPredictionController.php

namespace App\Domains\DataAnalyst\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Domains\DataAnalyst\Services\DropoutPredictionService;
use Illuminate\Support\Facades\Log;
use App\Domains\DataAnalyst\Services\ExportService;

class DropoutPredictionController extends Controller
{
    private DropoutPredictionService $predictionService;
    private ExportService $exportService;

    public function __construct(
        DropoutPredictionService $predictionService,
        ExportService $exportService
    ) {
        $this->predictionService = $predictionService;
        $this->exportService = $exportService;
    }

    /**
     * Predicciones de riesgo de deserción
     */
    public function getPredictions(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer',
                'min_risk_level' => 'sometimes|string|in:HIGH,MEDIUM,LOW'
            ]);

            $predictions = $this->predictionService->getDropoutPredictions($filters);

            return response()->json([
                'success' => true,
                'data' => $predictions,
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo predicciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Predicciones detalladas con toda la información
     */
    public function getDetailedPredictions(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer'
            ]);

            $predictions = $this->predictionService->getDetailedPredictions($filters);

            return response()->json([
                'success' => true,
                'data' => $predictions,
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo predicciones detalladas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estudiantes de alto riesgo
     */
    public function getHighRiskStudents(): JsonResponse
    {
        try {
            $students = $this->predictionService->getHighRiskStudents();

            return response()->json([
                'success' => true,
                'data' => [
                    'high_risk_students' => $students,
                    'count' => count($students)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo estudiantes de alto riesgo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Predicciones por grupo específico
     */
    public function getPredictionsByGroup($groupId): JsonResponse
    {
        try {
            $predictions = $this->predictionService->getPredictionsByGroup($groupId);

            return response()->json([
                'success' => true,
                'data' => $predictions,
                'group_id' => $groupId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo predicciones del grupo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estado del sistema de predicción
     */
    public function getSystemStatus(): JsonResponse
    {
        try {
            $metrics = $this->predictionService->getModelMetrics();
            $highRiskStudents = $this->predictionService->getHighRiskStudents();

            return response()->json([
                'success' => true,
                'data' => [
                    'model_status' => !empty($metrics) ? 'ACTIVE' : 'INACTIVE',
                    'model_accuracy' => $metrics['accuracy'] ?? 'N/A',
                    'high_risk_students_count' => count($highRiskStudents),
                    'last_updated' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo estado del sistema: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar predicciones de deserción
     */
    public function exportPredictions(Request $request)
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer',
                'min_risk_level' => 'sometimes|string|in:HIGH,MEDIUM,LOW',
                'format' => 'required|in:pdf,excel'
            ]);

            $format = $filters['format'];
            unset($filters['format']);

            $filePath = $this->exportService->exportDropoutPredictions($filters, $format);

            return $this->exportService->downloadFile($filePath);
        } catch (\Exception $e) {
            Log::error('Error en exportPredictions: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error exportando predicciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar predicciones detalladas
     */
    public function exportDetailedPredictions(Request $request)
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer',
                'format' => 'required|in:pdf,excel'
            ]);

            $format = $filters['format'];
            unset($filters['format']);

            $filePath = $this->exportService->exportDetailedDropoutPredictions($filters, $format);

            return $this->exportService->downloadFile($filePath);
        } catch (\Exception $e) {
            Log::error('Error en exportDetailedPredictions: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error exportando predicciones detalladas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar estudiantes de alto riesgo
     */
    public function exportHighRiskStudents(Request $request)
    {
        try {
            $filters = $request->validate([
                'format' => 'required|in:pdf,excel'
            ]);

            $format = $filters['format'];
            unset($filters['format']);

            $filePath = $this->exportService->exportHighRiskStudents($filters, $format);

            return $this->exportService->downloadFile($filePath);
        } catch (\Exception $e) {
            Log::error('Error en exportHighRiskStudents: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error exportando estudiantes de alto riesgo: ' . $e->getMessage()
            ], 500);
        }
    }
}