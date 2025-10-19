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

    /**
     * @OA\Get(
     *     path="/api/data-analyst/grades",
     *     summary="Listado de calificaciones con filtros",
     *     tags={"DataAnalyst - Grades"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Filtrar por curso específico",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="academic_period_id",
     *         in="query",
     *         description="Filtrar por período académico",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="grade_type",
     *         in="query",
     *         description="Filtrar por tipo de calificación",
     *         required=false,
     *         @OA\Schema(type="string", enum={"Partial", "Final", "Makeup"})
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar por registro",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar por registro",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Página actual",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Listado de calificaciones obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="obtained_grade", type="number", format="float", example=85.5),
     *                         @OA\Property(property="grade_type", type="string", example="Partial"),
     *                         @OA\Property(property="status", type="string", example="Recorded"),
     *                         @OA\Property(property="record_date", type="string", format="date-time"),
     *                         @OA\Property(property="user", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="student", type="object",
     *                                 @OA\Property(property="first_name", type="string", example="Juan"),
     *                                 @OA\Property(property="last_name", type="string", example="Pérez")
     *                             )
     *                         ),
     *                         @OA\Property(property="group", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Grupo A"),
     *                             @OA\Property(property="course", type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="Programación Básica"),
     *                                 @OA\Property(property="title", type="string", example="Introducción a la Programación")
     *                             )
     *                         ),
     *                         @OA\Property(property="evaluation", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="title", type="string", example="Examen Parcial 1")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="links", type="object"),
     *                 @OA\Property(property="meta", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/data-analyst/grades/stats/summary",
     *     summary="Obtener estadísticas generales de calificaciones",
     *     tags={"DataAnalyst - Grades"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Filtrar por curso específico",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="academic_period_id",
     *         in="query",
     *         description="Filtrar por período académico",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="grade_type",
     *         in="query",
     *         description="Filtrar por tipo de calificación",
     *         required=false,
     *         @OA\Schema(type="string", enum={"Partial", "Final", "Makeup"})
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar estadísticas",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar estadísticas",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_grades_recorded", type="integer", example=1567),
     *                 @OA\Property(property="average_grade", type="number", format="float", example=77.5),
     *                 @OA\Property(property="passing_rate", type="number", format="float", example=85.3),
     *                 @OA\Property(property="by_group", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="group_id", type="integer", example=12),
     *                         @OA\Property(property="group_name", type="string", example="Python Básico - Grupo A"),
     *                         @OA\Property(property="course_name", type="string", example="Python Básico"),
     *                         @OA\Property(property="total_grades", type="integer", example=45),
     *                         @OA\Property(property="average_grade", type="number", format="float", example=81.7),
     *                         @OA\Property(property="passing_rate", type="number", format="float", example=91.5)
     *                     )
     *                 ),
     *                 @OA\Property(property="top_performers", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="user_id", type="integer", example=45),
     *                         @OA\Property(property="first_name", type="string", example="Ana"),
     *                         @OA\Property(property="last_name", type="string", example="Martínez"),
     *                         @OA\Property(property="average_grade", type="number", format="float", example=95.5),
     *                         @OA\Property(property="total_grades", type="integer", example=8)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/data-analyst/grades/top-performers",
     *     summary="Obtener estudiantes con mejor rendimiento",
     *     tags={"DataAnalyst - Grades"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Filtrar por curso específico",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="academic_period_id",
     *         in="query",
     *         description="Filtrar por período académico",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Número máximo de estudiantes a retornar",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=50, default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de mejores estudiantes obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="user_id", type="integer", example=45),
     *                     @OA\Property(property="first_name", type="string", example="Ana"),
     *                     @OA\Property(property="last_name", type="string", example="Martínez"),
     *                     @OA\Property(property="average_grade", type="number", format="float", example=95.5),
     *                     @OA\Property(property="total_grades", type="integer", example=8),
     *                     @OA\Property(property="courses", type="array",
     *                         @OA\Items(type="string", example="Python Avanzado")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/data-analyst/grades/filter-options",
     *     summary="Obtener opciones de filtro disponibles",
     *     tags={"DataAnalyst - Grades"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Opciones de filtro obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="courses", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Programación Básica"),
     *                         @OA\Property(property="title", type="string", example="Introducción a la Programación")
     *                     )
     *                 ),
     *                 @OA\Property(property="academic_periods", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="2025-I")
     *                     )
     *                 ),
     *                 @OA\Property(property="grade_types", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="value", type="string", example="Partial"),
     *                         @OA\Property(property="label", type="string", example="Parcial")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getFilterOptions(Request $request): JsonResponse
    {
        try {
            $filterData = $this->gradeReportService->getFilterData();

            // Agregar tipos de calificación predefinidos
            $gradeTypes = [
                ['value' => 'Partial', 'label' => 'Parcial'],
                ['value' => 'Final', 'label' => 'Final'],
                ['value' => 'Makeup', 'label' => 'Recuperación']
            ];

            $filterData['grade_types'] = $gradeTypes;

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