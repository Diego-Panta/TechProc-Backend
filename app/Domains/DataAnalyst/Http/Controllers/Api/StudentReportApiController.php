<?php

namespace App\Domains\DataAnalyst\Http\Controllers\Api;

use App\Domains\DataAnalyst\Services\StudentReportService;
use App\Domains\DataAnalyst\Http\Requests\Api\StudentReportRequest;
use App\Domains\DataAnalyst\Http\Requests\Api\AdvancedReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentReportApiController
{
    public function __construct(
        private StudentReportService $studentReportService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/data-analyst/students",
     *     summary="Listado de estudiantes con filtros",
     *     tags={"DataAnalyst - Students"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar por nombre, apellido o email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="company",
     *         in="query",
     *         description="Filtrar por nombre de empresa",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por estado del estudiante",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive"})
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar por creación",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar por creación",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
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
     *         description="Listado de estudiantes obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="first_name", type="string", example="Juan"),
     *                         @OA\Property(property="last_name", type="string", example="Pérez"),
     *                         @OA\Property(property="email", type="string", example="juan@example.com"),
     *                         @OA\Property(property="phone", type="string", example="+123456789"),
     *                         @OA\Property(property="status", type="string", example="active"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="company", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Tech Solutions SAC")
     *                         ),
     *                         @OA\Property(property="enrollments_count", type="integer", example=5)
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
    public function index(StudentReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $students = $this->studentReportService->getStudentReport($filters);

            return response()->json([
                'success' => true,
                'data' => $students
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing students', [
                'filters' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el listado de estudiantes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/students/{studentId}",
     *     summary="Obtener detalle de un estudiante",
     *     tags={"DataAnalyst - Students"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="studentId",
     *         in="path",
     *         description="ID del estudiante",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del estudiante obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="Juan"),
     *                 @OA\Property(property="last_name", type="string", example="Pérez"),
     *                 @OA\Property(property="email", type="string", example="juan@example.com"),
     *                 @OA\Property(property="phone", type="string", example="+123456789"),
     *                 @OA\Property(property="document_number", type="string", example="12345678"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="company", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Tech Solutions SAC"),
     *                     @OA\Property(property="industry", type="string", example="Tecnología"),
     *                     @OA\Property(property="contact_name", type="string", example="María García"),
     *                     @OA\Property(property="contact_email", type="string", example="maria@techsolutions.com")
     *                 ),
     *                 @OA\Property(property="enrollments", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="enrollment_id", type="integer", example=1001),
     *                         @OA\Property(property="enrollment_date", type="string", format="date"),
     *                         @OA\Property(property="enrollment_type", type="string", example="new"),
     *                         @OA\Property(property="status", type="string", example="active"),
     *                         @OA\Property(property="academic_period", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="2025-I")
     *                         ),
     *                         @OA\Property(property="enrollment_details_count", type="integer", example=3)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estudiante no encontrado"
     *     )
     * )
     */
    public function show(int $studentId): JsonResponse
    {
        try {
            $student = $this->studentReportService->getStudentDetail($studentId);

            return response()->json([
                'success' => true,
                'data' => $student
            ]);

        } catch (\Exception $e) {
            Log::error('API Error showing student detail', [
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);

            if ($e->getCode() === 404 || str_contains($e->getMessage(), 'No query results')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el detalle del estudiante',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/students/stats/summary",
     *     summary="Obtener estadísticas de estudiantes",
     *     tags={"DataAnalyst - Students"},
     *     security={{"sanctum":{}}},
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
     *     @OA\Parameter(
     *         name="company_id",
     *         in="query",
     *         description="Filtrar por empresa específica",
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
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_students", type="integer", example=235),
     *                 @OA\Property(property="active_students", type="integer", example=198),
     *                 @OA\Property(property="inactive_students", type="integer", example=37),
     *                 @OA\Property(property="by_company", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="company_id", type="integer", example=3),
     *                         @OA\Property(property="company_name", type="string", example="Tech Solutions SAC"),
     *                         @OA\Property(property="student_count", type="integer", example=45)
     *                     )
     *                 ),
     *                 @OA\Property(property="enrollment_trend", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="period", type="string", example="2025-I"),
     *                         @OA\Property(property="enrolled", type="integer", example=180)
     *                     )
     *                 ),
     *                 @OA\Property(property="by_status", type="object",
     *                     @OA\Property(property="active", type="integer", example=198),
     *                     @OA\Property(property="inactive", type="integer", example=37)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getStatistics(StudentReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $statistics = $this->studentReportService->getStudentStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting student statistics', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas de estudiantes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

}