<?php

namespace App\Domains\DataAnalyst\Http\Controllers\Api;

use App\Domains\DataAnalyst\Services\CourseReportService;
use App\Domains\DataAnalyst\Http\Requests\Api\CourseReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CourseReportApiController
{
    public function __construct(
        private CourseReportService $courseReportService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/data-analyst/courses",
     *     summary="Listado de cursos con filtros",
     *     tags={"DataAnalyst - Courses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar por título, nombre o descripción",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filtrar por categoría",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         description="Filtrar por nivel del curso",
     *         required=false,
     *         @OA\Schema(type="string", enum={"basic", "intermediate", "advanced"})
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por estado del curso",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="bestseller",
     *         in="query",
     *         description="Filtrar cursos bestseller",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="featured",
     *         in="query",
     *         description="Filtrar cursos destacados",
     *         required=false,
     *         @OA\Schema(type="boolean")
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
     *         description="Listado de cursos obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Introducción a Python"),
     *                         @OA\Property(property="name", type="string", example="python-basico"),
     *                         @OA\Property(property="level", type="string", example="basic"),
     *                         @OA\Property(property="duration", type="number", format="float", example=40.5),
     *                         @OA\Property(property="sessions", type="integer", example=20),
     *                         @OA\Property(property="selling_price", type="number", format="float", example=299.99),
     *                         @OA\Property(property="discount_price", type="number", format="float", example=249.99),
     *                         @OA\Property(property="bestseller", type="boolean", example=true),
     *                         @OA\Property(property="featured", type="boolean", example=false),
     *                         @OA\Property(property="highest_rated", type="boolean", example=true),
     *                         @OA\Property(property="status", type="boolean", example=true),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="categories", type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="Programación")
     *                             )
     *                         ),
     *                         @OA\Property(property="enrollments_count", type="integer", example=156),
     *                         @OA\Property(property="course_offerings_count", type="integer", example=5),
     *                         @OA\Property(property="groups_count", type="integer", example=3)
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
    public function index(CourseReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $courses = $this->courseReportService->getCourseReport($filters);

            return response()->json([
                'success' => true,
                'data' => $courses
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing courses', [
                'filters' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el listado de cursos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/courses/{courseId}",
     *     summary="Obtener detalle de un curso",
     *     tags={"DataAnalyst - Courses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         description="ID del curso",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del curso obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Introducción a Python"),
     *                 @OA\Property(property="name", type="string", example="python-basico"),
     *                 @OA\Property(property="description", type="string", example="Curso introductorio de Python..."),
     *                 @OA\Property(property="level", type="string", example="basic"),
     *                 @OA\Property(property="duration", type="number", format="float", example=40.5),
     *                 @OA\Property(property="sessions", type="integer", example=20),
     *                 @OA\Property(property="selling_price", type="number", format="float", example=299.99),
     *                 @OA\Property(property="discount_price", type="number", format="float", example=249.99),
     *                 @OA\Property(property="prerequisites", type="string", example="Conocimientos básicos de programación"),
     *                 @OA\Property(property="certificate_name", type="boolean", example=true),
     *                 @OA\Property(property="certificate_issuer", type="string", example="Academia Digital"),
     *                 @OA\Property(property="bestseller", type="boolean", example=true),
     *                 @OA\Property(property="featured", type="boolean", example=false),
     *                 @OA\Property(property="highest_rated", type="boolean", example=true),
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="categories", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Programación"),
     *                         @OA\Property(property="slug", type="string", example="programacion")
     *                     )
     *                 ),
     *                 @OA\Property(property="instructors", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="first_name", type="string", example="Ana"),
     *                         @OA\Property(property="last_name", type="string", example="García"),
     *                         @OA\Property(property="email", type="string", example="ana@example.com")
     *                     )
     *                 ),
     *                 @OA\Property(property="course_offerings", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="course_offering_id", type="integer", example=1001),
     *                         @OA\Property(property="delivery_method", type="string", example="virtual"),
     *                         @OA\Property(property="academic_period", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="2025-I")
     *                         ),
     *                         @OA\Property(property="enrollment_details_count", type="integer", example=45)
     *                     )
     *                 ),
     *                 @OA\Property(property="course_contents_count", type="integer", example=15),
     *                 @OA\Property(property="groups_count", type="integer", example=3)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Curso no encontrado"
     *     )
     * )
     */
    public function show(int $courseId): JsonResponse
    {
        try {
            $course = $this->courseReportService->getCourseDetail($courseId);

            return response()->json([
                'success' => true,
                'data' => $course
            ]);

        } catch (\Exception $e) {
            Log::error('API Error showing course detail', [
                'course_id' => $courseId,
                'error' => $e->getMessage()
            ]);

            if ($e->getCode() === 404 || str_contains($e->getMessage(), 'No query results')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Curso no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el detalle del curso',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/courses/stats/summary",
     *     summary="Obtener estadísticas de cursos",
     *     tags={"DataAnalyst - Courses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filtrar por categoría específica",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         description="Filtrar por nivel específico",
     *         required=false,
     *         @OA\Schema(type="string", enum={"basic", "intermediate", "advanced"})
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
     *                 @OA\Property(property="total_courses", type="integer", example=45),
     *                 @OA\Property(property="active_courses", type="integer", example=40),
     *                 @OA\Property(property="inactive_courses", type="integer", example=5),
     *                 @OA\Property(property="by_level", type="object",
     *                     @OA\Property(property="basic", type="integer", example=18),
     *                     @OA\Property(property="intermediate", type="integer", example=15),
     *                     @OA\Property(property="advanced", type="integer", example=12)
     *                 ),
     *                 @OA\Property(property="by_category", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="category_id", type="integer", example=1),
     *                         @OA\Property(property="category_name", type="string", example="Programación"),
     *                         @OA\Property(property="course_count", type="integer", example=20)
     *                     )
     *                 ),
     *                 @OA\Property(property="most_enrolled", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="course_id", type="integer", example=1),
     *                         @OA\Property(property="course_title", type="string", example="Introducción a Python"),
     *                         @OA\Property(property="enrollments", type="integer", example=156)
     *                     )
     *                 ),
     *                 @OA\Property(property="bestsellers", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="course_id", type="integer", example=5),
     *                         @OA\Property(property="course_title", type="string", example="JavaScript Avanzado"),
     *                         @OA\Property(property="revenue", type="number", format="float", example=45890.50)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getStatistics(CourseReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $statistics = $this->courseReportService->getCourseStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting course statistics', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas de cursos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}