<?php
// app/Domains/DataAnalyst/Http/Controllers/ProgressAnalyticsController.php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Domains\DataAnalyst\Services\ProgressAnalysisService;
use App\Domains\DataAnalyst\Http\Requests\ProgressAnalysisRequest;
use App\Domains\DataAnalyst\Models\DataAnalytic;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProgressAnalyticsController
{
    public function __construct(
        private ProgressAnalysisService $progressService
    ) {}

    /**
     * Obtiene análisis de progreso para un estudiante
     */
    public function getStudentProgress(int $enrollmentId, ProgressAnalysisRequest $request): JsonResponse
    {
        try {
            $period = $request->input('period', '30d');
            $refresh = $request->boolean('refresh', false);

            $analytic = $this->progressService->analyzeStudentProgress($enrollmentId, $period);

            if (!$analytic) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo calcular el análisis de progreso'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatProgressResponse($analytic),
                'message' => 'Análisis de progreso obtenido correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al analizar progreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene estudiantes con problemas de progreso - CORREGIDO
     */
    public function getProgressIssues(ProgressAnalysisRequest $request): JsonResponse
    {
        try {
            $riskLevel = $request->input('risk_level', 'all');
            $period = $request->input('period', '30d');
            $limit = $request->input('limit', 50);

            // Consulta directa sin relaciones polimórficas problemáticas
            $query = DataAnalytic::where('analysis_type', 'progress')
                ->where('period', $period)
                ->where('analyzable_type', 'IncadevUns\\CoreDomain\\Models\\Enrollment');

            // Filtrar por nivel de riesgo - INCLUIR "critical" y "high" por defecto
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

            // Obtener información de estudiantes manualmente
            $enrollmentIds = $analytics->pluck('analyzable_id')->toArray();

            $enrollmentsWithUsers = DB::table('enrollments as e')
                ->select([
                    'e.id as enrollment_id',
                    'u.id as user_id',
                    'u.fullname',
                    'u.email',
                    'g.name as group_name',
                    'c.name as course_name'
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
                $completionRate = $metrics['completion_rate'] ?? 0;
                $moduleApprovalRate = $metrics['module_approval_rate'] ?? 0;

                return [
                    'enrollment_id' => $analytic->analyzable_id,
                    'overall_score' => $analytic->score,
                    'completion_rate' => $completionRate,
                    'module_approval_rate' => $moduleApprovalRate,
                    'risk_level' => $analytic->risk_level,
                    'student' => $enrollmentData ? [
                        'id' => $enrollmentData->user_id,
                        'fullname' => $enrollmentData->fullname,
                        'email' => $enrollmentData->email,
                    ] : [
                        'id' => null,
                        'fullname' => 'Estudiante no encontrado',
                        'email' => 'N/A',
                    ],
                    'course_info' => $enrollmentData ? [
                        'group_name' => $enrollmentData->group_name,
                        'course_name' => $enrollmentData->course_name,
                    ] : [
                        'group_name' => 'N/A',
                        'course_name' => 'N/A',
                    ],
                    'metrics' => $metrics,
                    'recommendations' => $analytic->recommendations,
                    'calculated_at' => $analytic->calculated_at->toISOString(),
                ];
            });

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
                'message' => 'Estudiantes con problemas de progreso obtenidos correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estudiantes con problemas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatea la respuesta
     */
    private function formatProgressResponse(DataAnalytic $analytic): array
    {
        $metrics = $analytic->metrics ?? [];

        return [
            'enrollment_id' => $analytic->analyzable_id,
            'analysis_type' => $analytic->analysis_type,
            'period' => $analytic->period,

            // Scores (escala 0-100 para consistencia)
            'overall_score' => $analytic->score, // 0-100
            'completion_rate' => $analytic->rate, // 0-100% (actividad)

            // Información de módulos
            'total_modules' => $analytic->total_events,
            'completed_modules' => $analytic->completed_events,

            // Nivel de riesgo
            'risk_level' => $analytic->risk_level,

            // Métricas detalladas (incluye escala original 0-20)
            'metrics' => array_merge($metrics, [
                'grade_scale' => '0-20',
                'completion_criteria' => 'módulos con actividad',
                'interpretation' => $this->getGradeInterpretation($metrics['average_grade'] ?? 0)
            ]),

            'trends' => $analytic->trends,
            'patterns' => $analytic->patterns,
            'comparisons' => $analytic->comparisons,
            'triggers' => $analytic->triggers,
            'recommendations' => $analytic->recommendations,
            'calculated_at' => $analytic->calculated_at->toISOString(),
        ];
    }

    private function getGradeInterpretation(float $grade): string
    {
        return match (true) {
            $grade >= 18 => 'Excelente',
            $grade >= 16 => 'Muy bueno',
            $grade >= 14 => 'Bueno',
            $grade >= 11 => 'Regular',
            default => 'Necesita mejora'
        };
    }
}
