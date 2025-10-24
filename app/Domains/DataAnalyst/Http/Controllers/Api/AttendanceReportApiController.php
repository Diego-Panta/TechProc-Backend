<?php

namespace App\Domains\DataAnalyst\Http\Controllers\Api;

use App\Domains\DataAnalyst\Services\AttendanceReportService;
use App\Domains\DataAnalyst\Http\Requests\Api\AttendanceReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceReportApiController
{
    public function __construct(
        private AttendanceReportService $attendanceReportService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/data-analyst/attendance",
     *     summary="Listado de registros de asistencia con filtros",
     *     tags={"DataAnalyst - Attendance"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="group_id",
     *         in="query",
     *         description="Filtrar por ID de grupo",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Filtrar por ID de curso",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         description="Filtrar por ID de estudiante",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar por clase",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar por clase",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="attendance_status",
     *         in="query",
     *         description="Filtrar por estado de asistencia",
     *         required=false,
     *         @OA\Schema(type="string", enum={"YES", "NO"})
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
     *         description="Listado de asistencias obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="attended", type="string", example="YES"),
     *                         @OA\Property(property="entry_time", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="exit_time", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="connected_minutes", type="integer", example=90),
     *                         @OA\Property(property="connection_ip", type="string", example="192.168.1.100"),
     *                         @OA\Property(property="device", type="string", example="Chrome Windows"),
     *                         @OA\Property(property="approximate_location", type="string", example="Lima, Peru"),
     *                         @OA\Property(property="connection_quality", type="string", example="GOOD"),
     *                         @OA\Property(property="observations", type="string", example="Asistió puntual"),
     *                         @OA\Property(property="cloud_synchronized", type="boolean", example=true),
     *                         @OA\Property(property="record_date", type="string", format="date-time"),
     *                         @OA\Property(property="class", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="class_name", type="string", example="Introducción a Python"),
     *                             @OA\Property(property="class_date", type="string", format="date"),
     *                             @OA\Property(property="start_time", type="string", format="date-time"),
     *                             @OA\Property(property="end_time", type="string", format="date-time"),
     *                             @OA\Property(property="platform", type="string", example="Zoom"),
     *                             @OA\Property(property="group", type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="Python Básico - Grupo A"),
     *                                 @OA\Property(property="course", type="object",
     *                                     @OA\Property(property="id", type="integer", example=1),
     *                                     @OA\Property(property="title", type="string", example="Python para Principiantes")
     *                                 )
     *                             )
     *                         ),
     *                         @OA\Property(property="student", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="first_name", type="string", example="Juan"),
     *                             @OA\Property(property="last_name", type="string", example="Pérez"),
     *                             @OA\Property(property="email", type="string", example="juan@example.com")
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
    public function index(AttendanceReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $attendanceData = $this->attendanceReportService->getAttendanceReport($filters);

            // Transformar datos para la respuesta API
            $transformedData = $attendanceData->getCollection()->map(function ($attendance) {
                $user = $attendance->groupParticipant->user;
                $student = $user->student ?? null;
                
                return [
                    'id' => $attendance->id,
                    'attended' => $attendance->attended,
                    'entry_time' => $attendance->entry_time,
                    'exit_time' => $attendance->exit_time,
                    'connected_minutes' => $attendance->connected_minutes,
                    'connection_ip' => $attendance->connection_ip,
                    'device' => $attendance->device,
                    'approximate_location' => $attendance->approximate_location,
                    'connection_quality' => $attendance->connection_quality,
                    'observations' => $attendance->observations,
                    'cloud_synchronized' => $attendance->cloud_synchronized,
                    'record_date' => $attendance->record_date,
                    'class' => [
                        'id' => $attendance->class->id,
                        'class_name' => $attendance->class->class_name,
                        'class_date' => $attendance->class->class_date,
                        'start_time' => $attendance->class->start_time,
                        'end_time' => $attendance->class->end_time,
                        'platform' => $attendance->class->platform,
                        'group' => [
                            'id' => $attendance->class->group->id,
                            'name' => $attendance->class->group->name,
                            'course' => [
                                'id' => $attendance->class->group->course->id,
                                'title' => $attendance->class->group->course->title
                            ]
                        ]
                    ],
                    'student' => [
                        'id' => $student->id ?? $user->id,
                        'first_name' => $student->first_name ?? $user->first_name,
                        'last_name' => $student->last_name ?? $user->last_name,
                        'email' => $student->email ?? $user->email
                    ]
                ];
            });

            $attendanceData->setCollection($transformedData);

            return response()->json([
                'success' => true,
                'data' => $attendanceData
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing attendance records', [
                'filters' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el listado de asistencias',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/attendance/stats/summary",
     *     summary="Obtener estadísticas de asistencia",
     *     tags={"DataAnalyst - Attendance"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="group_id",
     *         in="query",
     *         description="Filtrar por ID de grupo",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Filtrar por ID de curso",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         description="Filtrar por ID de estudiante",
     *         required=false,
     *         @OA\Schema(type="integer")
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
     *         description="Estadísticas de asistencia obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_classes", type="integer", example=120),
     *                 @OA\Property(property="total_attendances_recorded", type="integer", example=2345),
     *                 @OA\Property(property="present_attendances", type="integer", example=2050),
     *                 @OA\Property(property="absent_attendances", type="integer", example=295),
     *                 @OA\Property(property="average_attendance_rate", type="number", format="float", example=87.5),
     *                 @OA\Property(property="by_group", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="group_id", type="integer", example=12),
     *                         @OA\Property(property="group_name", type="string", example="Python Básico - Grupo A"),
     *                         @OA\Property(property="course_name", type="string", example="Python para Principiantes"),
     *                         @OA\Property(property="total_classes", type="integer", example=12),
     *                         @OA\Property(property="total_attendances", type="integer", example=156),
     *                         @OA\Property(property="present_count", type="integer", example=144),
     *                         @OA\Property(property="attendance_rate", type="number", format="float", example=92.3)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getStatistics(AttendanceReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $statistics = $this->attendanceReportService->getAttendanceStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting attendance statistics', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas de asistencia',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/attendance/trend",
     *     summary="Obtener tendencia de asistencia por fecha",
     *     tags={"DataAnalyst - Attendance"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="group_id",
     *         in="query",
     *         description="Filtrar por ID de grupo",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="Filtrar por ID de curso",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         description="Filtrar por ID de estudiante",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para la tendencia",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para la tendencia",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tendencia de asistencia obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="date", type="string", format="date", example="2025-10-01"),
     *                     @OA\Property(property="attendance_count", type="integer", example=156),
     *                     @OA\Property(property="present_count", type="integer", example=138),
     *                     @OA\Property(property="attendance_rate", type="number", format="float", example=88.2)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getTrend(AttendanceReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $trend = $this->attendanceReportService->getAttendanceTrend($filters);

            return response()->json([
                'success' => true,
                'data' => $trend
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting attendance trend', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la tendencia de asistencia',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/attendance/filters/options",
     *     summary="Obtener opciones para los filtros",
     *     tags={"DataAnalyst - Attendance"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Opciones de filtros obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="courses", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Python para Principiantes")
     *                     )
     *                 ),
     *                 @OA\Property(property="students", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="first_name", type="string", example="Juan"),
     *                         @OA\Property(property="last_name", type="string", example="Pérez"),
     *                         @OA\Property(property="email", type="string", example="juan@example.com")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getFilterOptions(): JsonResponse
    {
        try {
            $courses = $this->attendanceReportService->getCoursesForFilter();
            $students = $this->attendanceReportService->getStudentsForFilter();

            return response()->json([
                'success' => true,
                'data' => [
                    'courses' => $courses,
                    'students' => $students
                ]
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