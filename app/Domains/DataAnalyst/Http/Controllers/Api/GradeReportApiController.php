<?php

namespace App\Domains\DataAnalyst\Http\Controllers\Api;

use App\Domains\DataAnalyst\Services\GradeReportService;
use App\Domains\DataAnalyst\Http\Requests\Api\GradeReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GradeReportApiController
{
    public function __construct(
        private GradeReportService $gradeReportService
    ) {}

    public function index(GradeReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $grades = $this->gradeReportService->getGradeReport($filters);

            return response()->json([
                'success' => true,
                'data' => $grades
            ]);
        } catch (\Exception $e) {
            Log::error('API Error listing grades', [
                'filters' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el listado de calificaciones',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getStatistics(GradeReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $statistics = $this->gradeReportService->getGradeStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            Log::error('API Error getting grade statistics', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas de calificaciones',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getTopPerformers(GradeReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $topPerformers = $this->gradeReportService->getTopPerformers($filters);

            return response()->json([
                'success' => true,
                'data' => $topPerformers
            ]);
        } catch (\Exception $e) {
            Log::error('API Error getting top performers', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los estudiantes con mejor rendimiento',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getFilterOptions(Request $request): JsonResponse
    {
        try {
            $filterData = $this->gradeReportService->getFilterData();

            // CORREGIDO: Usar evaluation_types en lugar de grade_types
            $evaluationTypes = [
                ['value' => 'Exam', 'label' => 'Examen'],
                ['value' => 'Quiz', 'label' => 'Quiz'],
                ['value' => 'Project', 'label' => 'Proyecto'],
                ['value' => 'Assignment', 'label' => 'Tarea'],
                ['value' => 'Final', 'label' => 'Final']
            ];

            $filterData['evaluation_types'] = $evaluationTypes;

            return response()->json([
                'success' => true,
                'data' => $filterData
            ]);
        } catch (\Exception $e) {
            Log::error('API Error getting filter options', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las opciones de filtro',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
