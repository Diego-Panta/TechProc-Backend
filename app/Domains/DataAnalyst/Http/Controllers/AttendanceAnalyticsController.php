<?php
// app/Domains/DataAnalyst/Http/Controllers/AttendanceAnalyticsController.php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Domains\DataAnalyst\Services\AttendanceAnalyticsService;
use App\Domains\DataAnalyst\Http\Requests\AttendanceAnalysisRequest;
use App\Domains\DataAnalyst\Models\DataAnalytic;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use IncadevUns\CoreDomain\Models\Group;

class AttendanceAnalyticsController
{
    public function __construct(
        private AttendanceAnalyticsService $attendanceService
    ) {}

    /**
     * Obtiene análisis de asistencia para un estudiante específico
     */
    public function getStudentAttendance(int $enrollmentId, AttendanceAnalysisRequest $request): JsonResponse
    {
        try {
            $period = $request->input('period', '30d');
            $refresh = $request->boolean('refresh', false);

            $analytic = $this->attendanceService->getAttendanceAnalysis($enrollmentId, $period, $refresh);

            if (!$analytic) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo calcular el análisis de asistencia'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatAttendanceResponse($analytic),
                'message' => 'Análisis de asistencia obtenido correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al analizar asistencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatea la respuesta del análisis
     */
    private function formatAttendanceResponse(DataAnalytic $analytic): array
    {
        return [
            'enrollment_id' => $analytic->analyzable_id,
            'analysis_type' => $analytic->analysis_type,
            'period' => $analytic->period,
            'attendance_rate' => $analytic->rate,
            'risk_level' => $analytic->risk_level,
            'total_sessions' => $analytic->total_events,
            'attended_sessions' => $analytic->completed_events,
            'metrics' => $analytic->metrics,
            'trends' => $analytic->trends,
            'patterns' => $analytic->patterns,
            'comparisons' => $analytic->comparisons,
            'triggers' => $analytic->triggers,
            'recommendations' => $analytic->recommendations,
            'calculated_at' => $analytic->calculated_at->toISOString(),
            'status' => $analytic->status,
        ];
    }

    /**
     * Obtiene estudiantes con problemas de asistencia - CORREGIDO
     */
    public function getAttendanceIssues(AttendanceAnalysisRequest $request): JsonResponse
    {
        try {
            $riskLevel = $request->input('risk_level', 'high');
            $period = $request->input('period', '30d');
            $limit = $request->input('limit', 50);
            $page = $request->input('page', 1);

            // Consulta base sin relaciones problemáticas
            $query = DataAnalytic::where('analysis_type', 'attendance')
                ->where('period', $period)
                ->where('risk_level', $riskLevel);

            $analytics = $query->orderBy('rate', 'asc')
                ->paginate($limit, ['*'], 'page', $page);

            // Cargar relaciones manualmente
            $students = $analytics->map(function ($analytic) {
                try {
                    // Cargar enrollment manualmente
                    $enrollment = \IncadevUns\CoreDomain\Models\Enrollment::with([
                        'user:id,fullname,email,phone,dni',
                        'group.courseVersion.course:id,name'
                    ])->find($analytic->analyzable_id);

                    if (!$enrollment) {
                        return null;
                    }

                    $user = $enrollment->user;
                    $group = $enrollment->group;
                    $course = $group->courseVersion->course ?? null;

                    return [
                        'enrollment_id' => $analytic->analyzable_id,
                        'attendance_rate' => $analytic->rate,
                        'risk_level' => $analytic->risk_level,
                        'total_sessions' => $analytic->total_events,
                        'attended_sessions' => $analytic->completed_events,
                        'consecutive_absences' => $analytic->getMetric('consecutive_absences'),
                        'last_attendance_date' => $analytic->getMetric('last_attendance_date'),
                        'student' => [
                            'id' => $user->id ?? null,
                            'fullname' => $user->fullname ?? 'N/A',
                            'email' => $user->email ?? 'N/A',
                            'phone' => $user->phone ?? 'N/A',
                            'dni' => $user->dni ?? 'N/A',
                        ],
                        'course' => [
                            'name' => $course->name ?? 'Curso no disponible',
                            'group' => $group->name ?? 'Grupo no disponible',
                            'start_date' => $group->start_date ?? null,
                            'end_date' => $group->end_date ?? null,
                        ],
                    ];
                } catch (\Exception $e) {
                    logger('Error cargando relaciones para enrollment ' . $analytic->analyzable_id . ': ' . $e->getMessage());
                    return null;
                }
            })->filter();

            return response()->json([
                'success' => true,
                'data' => [
                    'students' => $students->values(),
                    'pagination' => $analytics->toArray(),
                    'filters' => [
                        'risk_level' => $riskLevel,
                        'period' => $period
                    ]
                ],
                'message' => 'Estudiantes con problemas de asistencia obtenidos correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estudiantes con problemas de asistencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene estadísticas de asistencia para un grupo - CORREGIDO
     */
    public function getGroupAttendance(int $groupId, AttendanceAnalysisRequest $request): JsonResponse
    {
        try {
            $period = $request->input('period', '30d');
            $refresh = $request->boolean('refresh', false);

            if ($refresh) {
                $this->attendanceService->calculateAttendanceForGroup($groupId, $period);
            }

            $stats = $this->attendanceService->getGroupAttendanceStats($groupId, $period);

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas de grupo obtenidas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de grupo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene estadísticas generales de asistencia - CORREGIDO
     */
    public function getAttendanceStatistics(): JsonResponse
    {
        try {
            // ⚠️ SOLO análisis de attendance para Enrollment
            $stats = DataAnalytic::selectRaw('
            COUNT(*) as total_analyses,
            AVG(rate) as overall_attendance_rate,
            SUM(CASE WHEN risk_level = "critical" THEN 1 ELSE 0 END) as critical_count,
            SUM(CASE WHEN risk_level = "high" THEN 1 ELSE 0 END) as high_count,
            SUM(CASE WHEN risk_level = "medium" THEN 1 ELSE 0 END) as medium_count,
            SUM(CASE WHEN risk_level = "low" THEN 1 ELSE 0 END) as low_count,
            SUM(CASE WHEN risk_level = "none" THEN 1 ELSE 0 END) as none_count
        ')->where('analysis_type', 'attendance')
                ->where('analyzable_type', 'IncadevUns\\CoreDomain\\Models\\Enrollment')
                ->whereHasMorph('analyzable', ['IncadevUns\\CoreDomain\\Models\\Enrollment'], function ($query) {
                    $query->where('academic_status', 'active');
                })->first();

            // Recent updates solo para attendance
            $recentUpdates = DataAnalytic::where('analysis_type', 'attendance')
                ->where('analyzable_type', 'IncadevUns\\CoreDomain\\Models\\Enrollment')
                ->where('calculated_at', '>=', now()->subHours(24))
                ->orderBy('calculated_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($analytic) {
                    try {
                        $enrollment = \IncadevUns\CoreDomain\Models\Enrollment::with('user:id,fullname')
                            ->find($analytic->analyzable_id);

                        $user = $enrollment->user ?? null;

                        return [
                            'id' => $analytic->id,
                            'enrollment_id' => $analytic->analyzable_id,
                            'rate' => $analytic->rate,
                            'risk_level' => $analytic->risk_level,
                            'calculated_at' => $analytic->calculated_at->toISOString(),
                            'student_name' => $user->fullname ?? 'N/A',
                        ];
                    } catch (\Exception $e) {
                        return [
                            'id' => $analytic->id,
                            'enrollment_id' => $analytic->analyzable_id,
                            'rate' => $analytic->rate,
                            'risk_level' => $analytic->risk_level,
                            'calculated_at' => $analytic->calculated_at->toISOString(),
                            'student_name' => 'Error cargando datos',
                        ];
                    }
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $stats ?? [
                        'total_analyses' => 0,
                        'overall_attendance_rate' => 0,
                        'critical_count' => 0,
                        'high_count' => 0,
                        'medium_count' => 0,
                        'low_count' => 0,
                        'none_count' => 0,
                    ],
                    'recent_updates' => $recentUpdates,
                    'last_updated' => now()->toISOString()
                ],
                'message' => 'Estadísticas de asistencia obtenidas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ejecuta el cálculo de asistencia para todos los grupos activos
     */
    public function calculateAllAttendance(): JsonResponse
    {
        try {
            $activeGroups = DB::table('groups')
                ->where('status', 'active')
                ->pluck('id');

            $results = [
                'total_groups' => 0,
                'total_students' => 0,
                'groups_processed' => []
            ];

            foreach ($activeGroups as $groupId) {
                try {
                    $groupResults = $this->attendanceService->calculateAttendanceForGroup($groupId);
                    $results['total_groups']++;
                    $results['total_students'] += $groupResults['total_processed'];
                    $results['groups_processed'][] = [
                        'group_id' => $groupId,
                        'results' => $groupResults
                    ];
                } catch (\Exception $e) {
                    // Continuar con el siguiente grupo si hay error
                    continue;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Cálculo de asistencia completado para todos los grupos activos'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el cálculo masivo: ' . $e->getMessage()
            ], 500);
        }
    }
}
