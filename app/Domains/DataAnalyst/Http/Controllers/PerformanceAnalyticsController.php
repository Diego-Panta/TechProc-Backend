<?php
// app/Domains/DataAnalyst/Http/Controllers/PerformanceAnalyticsController.php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Domains\DataAnalyst\Services\PerformanceAnalysisService;
use App\Domains\DataAnalyst\Http\Requests\PerformanceAnalysisRequest;
use App\Domains\DataAnalyst\Models\DataAnalytic;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceAnalyticsController
{
    public function __construct(
        private PerformanceAnalysisService $performanceService
    ) {}

    /**
     * Obtiene análisis de rendimiento para un grupo
     */
    public function getGroupPerformance(int $groupId, PerformanceAnalysisRequest $request): JsonResponse
    {
        try {
            $period = $request->input('period', '30d');
            $refresh = $request->boolean('refresh', false);

            $analytic = $this->performanceService->analyzeGroupPerformance($groupId, $period);

            if (!$analytic) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo calcular el análisis de rendimiento'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatPerformanceResponse($analytic),
                'message' => 'Análisis de rendimiento obtenido correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error("Error en getGroupPerformance: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al analizar rendimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene grupos con problemas de rendimiento
     */
    public function getPerformanceIssues(PerformanceAnalysisRequest $request): JsonResponse
    {
        try {
            $riskLevel = $request->input('risk_level', 'critical_high');
            $period = $request->input('period', '30d');
            $limit = $request->input('limit', 50);

            // Consulta directa sin relaciones polimórficas problemáticas
            $query = DataAnalytic::where('analysis_type', 'performance')
                ->where('period', $period)
                ->where('analyzable_type', 'IncadevUns\\CoreDomain\\Models\\Group');

            // Filtrar por nivel de riesgo
            if ($riskLevel !== 'all') {
                if ($riskLevel === 'critical_high') {
                    $query->whereIn('risk_level', ['critical', 'high']);
                } else {
                    $query->where('risk_level', $riskLevel);
                }
            } else {
                // Por defecto, mostrar critical y high
                $query->whereIn('risk_level', ['critical', 'high', 'medium']);
            }

            $analytics = $query->orderBy('risk_level', 'desc')
                ->orderBy('score', 'asc')
                ->limit($limit)
                ->get();

            // Obtener información de grupos manualmente
            $groupIds = $analytics->pluck('analyzable_id')->toArray();

            $groupsWithInfo = DB::table('groups as g')
                ->select([
                    'g.id as group_id',
                    'g.name as group_name',
                    'g.start_date',
                    'g.end_date',
                    'g.status as group_status',
                    'c.name as course_name',
                    'cv.version as course_version'
                ])
                ->join('course_versions as cv', 'g.course_version_id', '=', 'cv.id')
                ->join('courses as c', 'cv.course_id', '=', 'c.id')
                ->whereIn('g.id', $groupIds)
                ->get()
                ->keyBy('group_id');

            $groups = $analytics->map(function ($analytic) use ($groupsWithInfo) {
                $groupData = $groupsWithInfo[$analytic->analyzable_id] ?? null;

                $metrics = $analytic->metrics ?? [];
                $averageGrade = $metrics['average_grade'] ?? 0;
                $completionRate = $metrics['completion_rate'] ?? 0;
                $attendanceRate = $metrics['attendance_rate'] ?? 0;

                return [
                    'group_id' => $analytic->analyzable_id,
                    'overall_score' => $analytic->score,
                    'average_grade' => $averageGrade,
                    'completion_rate' => $completionRate,
                    'attendance_rate' => $attendanceRate,
                    'risk_level' => $analytic->risk_level,
                    'group_info' => $groupData ? [
                        'name' => $groupData->group_name,
                        'start_date' => $groupData->start_date,
                        'end_date' => $groupData->end_date,
                        'status' => $groupData->group_status,
                    ] : [
                        'name' => 'Grupo no encontrado',
                        'start_date' => 'N/A',
                        'end_date' => 'N/A',
                        'status' => 'N/A',
                    ],
                    'course_info' => $groupData ? [
                        'course_name' => $groupData->course_name,
                        'version' => $groupData->course_version,
                    ] : [
                        'course_name' => 'N/A',
                        'version' => 'N/A',
                    ],
                    'metrics' => $metrics,
                    'recommendations' => $analytic->recommendations,
                    'calculated_at' => $analytic->calculated_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'groups' => $groups->values(),
                    'total' => $groups->count(),
                    'risk_distribution' => [
                        'critical' => $analytics->where('risk_level', 'critical')->count(),
                        'high' => $analytics->where('risk_level', 'high')->count(),
                        'medium' => $analytics->where('risk_level', 'medium')->count(),
                        'low' => $analytics->where('risk_level', 'low')->count(),
                    ],
                    'filters' => [
                        'risk_level' => $riskLevel,
                        'period' => $period,
                        'limit' => $limit
                    ]
                ],
                'message' => 'Grupos con problemas de rendimiento obtenidos correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error("Error en getPerformanceIssues: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener grupos con problemas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene distribución de calificaciones para un grupo
     */
    public function getGradeDistribution(int $groupId, PerformanceAnalysisRequest $request): JsonResponse
    {
        try {
            $distribution = $this->performanceService->getGradeDistribution($groupId);

            return response()->json([
                'success' => true,
                'data' => $distribution,
                'message' => 'Distribución de calificaciones obtenida correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error("Error en getGradeDistribution: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener distribución de calificaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene efectividad de instructores
     */
    public function getInstructorEffectiveness(int $groupId, PerformanceAnalysisRequest $request): JsonResponse
    {
        try {
            $effectiveness = $this->performanceService->getInstructorEffectiveness($groupId);

            return response()->json([
                'success' => true,
                'data' => $effectiveness,
                'message' => 'Efectividad de instructores obtenida correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error("Error en getInstructorEffectiveness: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener efectividad de instructores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene desempeño por módulo
     */
    public function getModulePerformance(int $groupId, PerformanceAnalysisRequest $request): JsonResponse
    {
        try {
            $modulePerformance = $this->performanceService->getModulePerformance($groupId);

            return response()->json([
                'success' => true,
                'data' => $modulePerformance,
                'message' => 'Desempeño por módulo obtenido correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error("Error en getModulePerformance: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener desempeño por módulo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatea la respuesta de rendimiento
     */
    private function formatPerformanceResponse(DataAnalytic $analytic): array
    {
        $metrics = $analytic->metrics ?? [];

        return [
            'group_id' => $analytic->analyzable_id,
            'analysis_type' => $analytic->analysis_type,
            'period' => $analytic->period,

            // Scores principales
            'overall_score' => $analytic->score, // 0-100
            'completion_rate' => $analytic->rate, // 0-100%

            // Información de estudiantes
            'total_students' => $analytic->total_events,
            'active_students' => $analytic->completed_events,

            // Nivel de riesgo
            'risk_level' => $analytic->risk_level,

            // Métricas detalladas
            'metrics' => array_merge($metrics, [
                'grade_scale' => '0-20',
                'interpretation' => $this->getPerformanceInterpretation($analytic->score)
            ]),

            'trends' => $analytic->trends,
            'patterns' => $analytic->patterns,
            'comparisons' => $analytic->comparisons,
            'triggers' => $analytic->triggers,
            'recommendations' => $analytic->recommendations,
            'calculated_at' => $analytic->calculated_at->toISOString(),
        ];
    }

    private function getPerformanceInterpretation(float $score): string
    {
        return match (true) {
            $score >= 90 => 'Excelente rendimiento',
            $score >= 80 => 'Buen rendimiento',
            $score >= 70 => 'Rendimiento aceptable',
            $score >= 60 => 'Rendimiento bajo',
            default => 'Rendimiento crítico'
        };
    }
}