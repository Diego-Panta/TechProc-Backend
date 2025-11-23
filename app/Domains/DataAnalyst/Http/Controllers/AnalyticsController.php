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
                'course_version_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',  // ✅ FILTRO DE FECHA
                'end_date' => 'sometimes|date',    // ✅ FILTRO DE FECHA
                'module_id' => 'sometimes|integer',
                'min_grade' => 'sometimes|numeric|min:0|max:20',
                'max_grade' => 'sometimes|numeric|min:0|max:20'
            ]);

            $metrics = $this->analyticsService->getProgressMetrics($filters);

            return response()->json([
                'success' => true,
                'data' => $metrics,
                'filters' => $filters,
                'notes' => [
                    'Los filtros de fecha aplican a:',
                    '- Sesiones de clase (para completitud de módulos)',
                    '- Fechas de examen (para calificaciones)',
                    'Sin filtros = Todos los datos acumulados'
                ]
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
                'end_date' => 'sometimes|date',
                'exam_start_date' => 'sometimes|date',
                'exam_end_date' => 'sometimes|date',
                'attendance_start_date' => 'sometimes|date',
                'attendance_end_date' => 'sometimes|date',
                'min_grade' => 'sometimes|numeric|min:0|max:20',
                'max_grade' => 'sometimes|numeric|min:0|max:20',
                'only_active' => 'sometimes|boolean'
            ]);

            $metrics = $this->analyticsService->getPerformanceMetrics($filters);

            return response()->json([
                'success' => true,
                'data' => $metrics,
                'filters' => $filters,
                'notes' => [
                    'Filtros aplicados a:',
                    '- Exámenes (fechas de examen)',
                    '- Asistencias (fechas de clase)',
                    '- Calificaciones (rangos)',
                    'Sin filtros = Todos los datos acumulados'
                ]
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
     * MÓDULO ASISTENCIA - Gráficas específicas VIABLES
     */

    /**
     * Distribución de estados de asistencia (Gráfico de Pastel)
     */
    public function getAttendanceStatusDistribution(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date'
            ]);

            $data = $this->analyticsService->getAttendanceStatusDistribution($filters);

            return response()->json([
                'success' => true,
                'data' => $data,
                'filters' => $filters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo distribución de asistencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tendencia semanal de ausencias (Gráfico de Líneas)
     */
    public function getWeeklyAbsenceTrends(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date'
            ]);

            $data = $this->analyticsService->getWeeklyAbsenceTrends($filters);

            return response()->json([
                'success' => true,
                'data' => $data,
                'filters' => $filters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo tendencias de ausencias: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calendario de asistencia (Heatmap)
     */
    public function getAttendanceCalendar(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date'
            ]);

            $data = $this->analyticsService->getAttendanceCalendar($filters);

            return response()->json([
                'success' => true,
                'data' => $data,
                'filters' => $filters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo calendario de asistencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * MÓDULO RENDIMIENTO - Gráficas específicas VIABLES
     */

    /**
     * Distribución de calificaciones (Histograma)
     */
    public function getGradeDistribution(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date',
                'min_grade' => 'sometimes|numeric|min:0|max:20',
                'max_grade' => 'sometimes|numeric|min:0|max:20'
            ]);

            $data = $this->analyticsService->getGradeDistribution($filters);

            return response()->json([
                'success' => true,
                'data' => $data,
                'scale_info' => [
                    'min_grade' => 0,
                    'max_grade' => 20,
                    'passing_grade' => 11,
                    'description' => 'Escala de calificaciones de 0 a 20, donde 11 es la nota mínima aprobatoria'
                ],
                'filters' => $filters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo distribución de calificaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Correlación asistencia vs calificación (Gráfico de Dispersión)
     */
    public function getAttendanceGradeCorrelation(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date',
                'attendance_start_date' => 'sometimes|date',
                'attendance_end_date' => 'sometimes|date',
                'exam_start_date' => 'sometimes|date',
                'exam_end_date' => 'sometimes|date',
                'debug' => 'sometimes|boolean'  // ✅ NUEVO PARÁMETRO DEBUG
            ]);

            $data = $this->analyticsService->getAttendanceGradeCorrelation($filters);

            $response = [
                'success' => true,
                'data' => $data,
                'scale_info' => [
                    'min_grade' => 0,
                    'max_grade' => 20,
                    'passing_grade' => 11
                ],
                'filters' => $filters,
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo correlación asistencia-calificación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificación de consistencia de datos
     */
    public function getDataConsistencyCheck(Request $request): JsonResponse
    {
        try {
            $attendance = $this->analyticsService->getAttendanceMetrics();
            $progress = $this->analyticsService->getProgressMetrics();
            $performance = $this->analyticsService->getPerformanceMetrics();

            $consistencyReport = [
                'student_count_consistency' => [
                    'attendance' => $attendance['data']['summary']['total_students'],
                    'progress' => count($progress['data']['grade_consistency']),
                    'performance' => $performance['data']['summary']['total_students'],
                    'consistent' => $attendance['data']['summary']['total_students'] ===
                        count($progress['data']['grade_consistency']) &&
                        $attendance['data']['summary']['total_students'] ===
                        $performance['data']['summary']['total_students']
                ],
                'expected_students' => 4,
                'notes' => [
                    'Se esperan exactamente 4 estudiantes activos en todos los endpoints',
                    'Cualquier discrepancia indica problemas en las vistas SQL'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $consistencyReport
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error en verificación de datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rendimiento por grupo (Gráfico Radar - Variante simplificada)
     */
    public function getGroupPerformanceRadar(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer'
            ]);

            $data = $this->analyticsService->getGroupPerformanceRadar($filters);

            return response()->json([
                'success' => true,
                'data' => $data,
                'scale_info' => [
                    'min_grade' => 0,
                    'max_grade' => 20,
                    'passing_grade' => 11
                ],
                'filters' => $filters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo rendimiento por grupo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * MÓDULO PROGRESO - Gráficas específicas VIABLES
     */

    /**
     * Evolución de calificaciones (Gráfico de Líneas)
     */
    public function getGradeEvolution(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'user_id' => 'sometimes|integer',
                'course_version_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',  // Filtro por fecha de examen
                'end_date' => 'sometimes|date'     // Filtro por fecha de examen
            ]);

            $data = $this->analyticsService->getGradeEvolution($filters);

            return response()->json([
                'success' => true,
                'data' => $data,
                'filters' => $filters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo evolución de calificaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista de grupos para filtros
     */
    public function getGroupsList(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'status' => 'sometimes|string|in:active,inactive,enrolling',
                'course_version_id' => 'sometimes|integer',
                'active_only' => 'sometimes|boolean'
            ]);

            $groups = $this->analyticsService->getGroupsList($filters);

            return response()->json([
                'success' => true,
                'data' => $groups,
                'meta' => [
                    'total_groups' => count($groups),
                    'active_groups' => count(array_filter($groups, fn($g) => $g['status'] === 'active'))
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getGroupsList: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo lista de grupos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Grupos activos para filtros (endpoint simplificado)
     */
    public function getActiveGroups(Request $request): JsonResponse
    {
        try {
            $groups = $this->analyticsService->getActiveGroups();

            return response()->json([
                'success' => true,
                'data' => $groups,
                'meta' => [
                    'total_active_groups' => count($groups)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getActiveGroups: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo grupos activos: ' . $e->getMessage()
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
