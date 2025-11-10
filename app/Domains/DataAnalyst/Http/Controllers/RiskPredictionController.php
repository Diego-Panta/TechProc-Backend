<?php
// app/Domains/DataAnalyst/Http/Controllers/RiskPredictionController.php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Domains\DataAnalyst\Services\RiskPredictionService;
use App\Domains\DataAnalyst\Models\DataAnalytic;
use App\Domains\DataAnalyst\Http\Requests\RiskAnalysisRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RiskPredictionController
{
    public function __construct(
        private RiskPredictionService $riskService
    ) {}

    /**
     * Obtiene el análisis de riesgo para una matrícula específica
     */
    public function getStudentRisk(int $enrollmentId, RiskAnalysisRequest $request): JsonResponse
    {
        try {
            $period = $request->input('period', '30d');
            $refresh = $request->boolean('refresh', false);

            if ($refresh) {
                $analytic = $this->riskService->calculateAndSaveRiskAnalysis($enrollmentId, $period);
            } else {
                $analytic = DataAnalytic::forAnalyzable('IncadevUns\\CoreDomain\\Models\\Enrollment', $enrollmentId)
                    ->ofType('risk_prediction')
                    ->ofPeriod($period)
                    ->first();

                if (!$analytic) {
                    $analytic = $this->riskService->calculateAndSaveRiskAnalysis($enrollmentId, $period);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatRiskResponse($analytic),
                'message' => 'Análisis de riesgo obtenido correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular el riesgo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene estudiantes en riesgo por nivel
     */
    public function getAtRiskStudents(RiskAnalysisRequest $request): JsonResponse
    {
        try {
            $riskLevel = $request->input('risk_level', 'critical_high');
            $period = $request->input('period', '30d');
            $limit = $request->input('limit', 50);

            $query = DataAnalytic::where('analysis_type', 'risk_prediction')
                ->where('period', $period)
                ->where('analyzable_type', 'IncadevUns\\CoreDomain\\Models\\Enrollment');

            // Filtrar por nivel de riesgo
            if ($riskLevel === 'critical_high') {
                $query->whereIn('risk_level', ['critical', 'high']);
            } elseif ($riskLevel !== 'all') {
                $query->where('risk_level', $riskLevel);
            }

            $analytics = $query->orderBy('score', 'desc')
                ->orderBy('risk_level', 'desc')
                ->limit($limit)
                ->get();

            // Obtener información de estudiantes
            $enrollmentIds = $analytics->pluck('analyzable_id')->toArray();

            $enrollmentsWithUsers = DB::table('enrollments as e')
                ->select([
                    'e.id as enrollment_id',
                    'u.id as user_id',
                    'u.fullname',
                    'u.email',
                    'u.phone',
                    'u.dni',
                    'g.name as group_name',
                    'c.name as course_name',
                    'e.payment_status',
                    'e.academic_status'
                ])
                ->join('users as u', 'e.user_id', '=', 'u.id')
                ->join('groups as g', 'e.group_id', '=', 'g.id')
                ->join('course_versions as cv', 'g.course_version_id', '=', 'cv.id')
                ->join('courses as c', 'cv.course_id', '=', 'c.id')
                ->whereIn('e.id', $enrollmentIds)
                ->get()
                ->keyBy('enrollment_id');

            $students = $analytics->map(function ($analytic) use ($enrollmentsWithUsers) {
                $enrollmentData = $enrollmentsWithUsers[$analytic->analyzable_id] ?? null;
                $metrics = $analytic->metrics ?? [];

                return [
                    'enrollment_id' => $analytic->analyzable_id,
                    'risk_level' => $analytic->risk_level,
                    'risk_score' => $analytic->score,
                    'dropout_probability' => $analytic->rate,
                    'component_scores' => $metrics['component_scores'] ?? [],
                    'risk_factors' => $metrics['risk_factors'] ?? [],
                    'student' => $enrollmentData ? [
                        'id' => $enrollmentData->user_id,
                        'fullname' => $enrollmentData->fullname,
                        'email' => $enrollmentData->email,
                        'phone' => $enrollmentData->phone,
                        'dni' => $enrollmentData->dni,
                    ] : null,
                    'course_info' => $enrollmentData ? [
                        'group_name' => $enrollmentData->group_name,
                        'course_name' => $enrollmentData->course_name,
                        'payment_status' => $enrollmentData->payment_status,
                        'academic_status' => $enrollmentData->academic_status,
                    ] : null,
                    'triggers' => $analytic->triggers,
                    'recommendations' => $analytic->recommendations,
                    'calculated_at' => $analytic->calculated_at->toISOString(),
                ];
            })->filter(fn($student) => $student['student'] !== null);

            return response()->json([
                'success' => true,
                'data' => [
                    'students' => $students->values(),
                    'total' => $students->count(),
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
                'message' => 'Estudiantes en riesgo obtenidos correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estudiantes en riesgo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ejecuta el cálculo de riesgo para todos los estudiantes activos
     */
    public function calculateAllRisks(RiskAnalysisRequest $request): JsonResponse
    {
        try {
            $period = $request->input('period', '30d');
            $results = $this->riskService->calculateRiskForAllActiveStudents($period);

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Cálculo de riesgo completado para todos los estudiantes activos'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el cálculo masivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene estadísticas generales de riesgo
     */
    public function getRiskStatistics(RiskAnalysisRequest $request): JsonResponse
    {
        try {
            $period = $request->input('period', '30d');

            // Estadísticas usando DataAnalytic
            $stats = DataAnalytic::where('analysis_type', 'risk_prediction')
                ->where('period', $period)
                ->selectRaw('
                    COUNT(*) as total_analytics,
                    AVG(score) as average_risk_score,
                    SUM(CASE WHEN risk_level = "critical" THEN 1 ELSE 0 END) as critical_count,
                    SUM(CASE WHEN risk_level = "high" THEN 1 ELSE 0 END) as high_count,
                    SUM(CASE WHEN risk_level = "medium" THEN 1 ELSE 0 END) as medium_count,
                    SUM(CASE WHEN risk_level = "low" THEN 1 ELSE 0 END) as low_count,
                    SUM(CASE WHEN risk_level = "none" THEN 1 ELSE 0 END) as none_count
                ')
                ->first();

            // Estudiantes activos totales
            $totalActiveStudents = DB::table('enrollments')
                ->where('academic_status', 'active')
                ->count();

            $recentUpdates = DataAnalytic::with(['analyzable'])
                ->where('analysis_type', 'risk_prediction')
                ->where('calculated_at', '>=', now()->subHours(24))
                ->orderBy('calculated_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $stats,
                    'total_active_students' => $totalActiveStudents,
                    'coverage_percentage' => $totalActiveStudents > 0 ? 
                        round(($stats->total_analytics / $totalActiveStudents) * 100, 2) : 0,
                    'recent_updates' => $recentUpdates,
                    'last_updated' => now()->toISOString()
                ],
                'message' => 'Estadísticas de riesgo obtenidas correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatea la respuesta de riesgo
     */
    private function formatRiskResponse(DataAnalytic $analytic): array
    {
        $metrics = $analytic->metrics ?? [];

        return [
            'enrollment_id' => $analytic->analyzable_id,
            'analysis_type' => $analytic->analysis_type,
            'period' => $analytic->period,
            'risk_level' => $analytic->risk_level,
            'risk_score' => $analytic->score,
            'dropout_probability' => $analytic->rate,
            'total_risk_events' => $analytic->total_events,
            'completed_interventions' => $analytic->completed_events,
            'component_scores' => $metrics['component_scores'] ?? [],
            'risk_factors' => $metrics['risk_factors'] ?? [],
            'prediction_confidence' => $metrics['prediction_confidence'] ?? 0,
            'last_activity' => $metrics['last_activity_date'] ?? null,
            'days_inactive' => $metrics['days_since_last_activity'] ?? 0,
            'trends' => $analytic->trends,
            'patterns' => $analytic->patterns,
            'comparisons' => $analytic->comparisons,
            'triggers' => $analytic->triggers,
            'recommendations' => $analytic->recommendations,
            'calculated_at' => $analytic->calculated_at->toISOString(),
        ];
    }
}