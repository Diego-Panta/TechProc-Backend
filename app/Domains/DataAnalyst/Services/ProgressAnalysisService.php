<?php
// app/Domains/DataAnalyst/Services/ProgressAnalysisService.php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Models\DataAnalytic;
use App\Domains\DataAnalyst\Repositories\ProgressDataRepository;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProgressAnalysisService
{
    public function __construct(
        private ProgressDataRepository $progressRepository
    ) {}

    /**
     * Analiza el progreso de un estudiante con manejo de errores
     */
    public function analyzeStudentProgress(int $enrollmentId, string $period = '30d'): ?DataAnalytic
    {
        try {
            $enrollment = $this->progressRepository->getEnrollment($enrollmentId);

            if (!$enrollment) {
                throw new \Exception("Matrícula no encontrada o inactiva: {$enrollmentId}");
            }

            $progressData = $this->progressRepository->getStudentProgressData($enrollmentId, $period);

            // DEBUG DETALLADO
            Log::info("=== PROGRESS ANALYSIS DEBUG ===");
            Log::info("Enrollment: {$enrollmentId}, Period: {$period}");
            Log::info("Grades count: " . count($progressData['grades'] ?? []));
            Log::info("Modules count: " . count($progressData['modules'] ?? []));
            Log::info("Completed modules count: " . count($progressData['completed_modules'] ?? []));

            // Log detallado de módulos y calificaciones
            foreach ($progressData['modules'] ?? [] as $module) {
                Log::info("Course module: {$module->id} - {$module->title}");
            }

            foreach ($progressData['completed_modules'] ?? [] as $completed) {
                Log::info("Completed module: {$completed->module_id} - {$completed->module_title} (Grade: {$completed->best_grade})");
            }

            if (empty($progressData['grades']) && empty($progressData['modules'])) {
                Log::warning("No data found for enrollment {$enrollmentId}");
                return $this->createEmptyProgressAnalytic($enrollmentId, $period);
            }

            $analysis = $this->calculateProgressAnalysis($progressData);
            $riskLevel = $this->assessProgressRisk($analysis);

            Log::info("Final analysis for enrollment {$enrollmentId}:", [
                'completion_rate' => $analysis['completion_rate'],
                'completed_modules' => $analysis['completed_modules'],
                'total_modules' => $analysis['total_modules'],
                'risk_level' => $riskLevel
            ]);

            return DataAnalytic::updateOrCreate(
                [
                    'analyzable_type' => 'IncadevUns\\CoreDomain\\Models\\Enrollment',
                    'analyzable_id' => $enrollmentId,
                    'analysis_type' => 'progress',
                    'period' => $period,
                ],
                [
                    'score' => $analysis['overall_score_normalized'],
                    'rate' => $analysis['completion_rate'],
                    'total_events' => $analysis['total_modules'],
                    'completed_events' => $analysis['completed_modules'],
                    'risk_level' => $riskLevel,
                    'metrics' => $analysis,
                    'trends' => $this->calculateProgressTrends($progressData['grades']),
                    'patterns' => $this->identifyProgressPatterns($analysis),
                    'comparisons' => $this->compareWithGroup($enrollment->group_id, $analysis),
                    'triggers' => $this->identifyRiskTriggers($analysis, $riskLevel),
                    'recommendations' => $this->generateRecommendations($analysis, $riskLevel),
                    'calculated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error("Error analizando progreso estudiante {$enrollmentId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calcula el análisis de progreso principal - CORREGIDO CON ESCALA 0-20
     */
    private function calculateProgressAnalysis(array $progressData): array
    {
        $grades = $progressData['grades'] ?? [];
        $modules = $progressData['modules'] ?? [];
        $completedModules = $progressData['completed_modules'] ?? [];

        // Calcular métricas base en escala 0-20
        $overallScore = $this->calculateOverallScore($grades);
        $completionRate = $this->calculateCompletionRate($modules, $completedModules);
        $completionVelocity = $this->calculateCompletionVelocity($modules, $completedModules);
        $gradeConsistency = $this->calculateGradeConsistency($grades);
        $improvementTrend = $this->calculateImprovementTrend($grades);

        // Nueva métrica: Tasa de aprobación de módulos
        $moduleApprovalRate = $this->calculateModuleApprovalRate($completedModules);

        // Convertir a escala 0-100 para el score principal
        $overallScoreNormalized = ($overallScore / 20) * 100;

        $analysis = [
            // Métricas en escala original (0-20)
            'overall_score_original' => $overallScore,
            'average_grade' => $overallScore,

            // Métricas normalizadas (0-100)
            'overall_score_normalized' => round($overallScoreNormalized, 2),

            // Progreso y actividad
            'completion_rate' => $completionRate,
            'total_modules' => count($modules),
            'completed_modules' => count($completedModules),
            'completion_velocity' => $completionVelocity,
            'module_approval_rate' => $moduleApprovalRate,

            // Calidad académica
            'grade_consistency' => $gradeConsistency,
            'improvement_trend' => $improvementTrend,
            'last_activity' => $this->getLastActivity($grades, $completedModules),

            // Información de escala
            'grade_scale' => '0-20',
            'normalized_scale' => '0-100'
        ];

        Log::info("Updated analysis results:", $analysis);

        return $analysis;
    }

    /**
     * Calcula tasa de aprobación de módulos
     */
    private function calculateModuleApprovalRate(array $completedModules): float
    {
        if (empty($completedModules)) {
            return 0.0;
        }

        $approvedCount = 0;
        foreach ($completedModules as $module) {
            if (isset($module->is_approved) && $module->is_approved) {
                $approvedCount++;
            }
        }

        $approvalRate = ($approvedCount / count($completedModules)) * 100;
        return round($approvalRate, 2);
    }

    /**
     * Calcula puntuación general en escala 0-20
     */
    private function calculateOverallScore(array $grades): float
    {
        if (empty($grades)) return 0.0;

        $validGrades = array_filter(array_column($grades, 'grade'), function ($grade) {
            return is_numeric($grade) && $grade >= 0 && $grade <= 20;
        });

        if (empty($validGrades)) return 0.0;

        return round(array_sum($validGrades) / count($validGrades), 2);
    }

    /**
     * Calcula tasa de completación - CORREGIDO
     */
    private function calculateCompletionRate(array $modules, array $completedModules): float
    {
        if (empty($modules)) {
            return 0.0;
        }

        $completedCount = count($completedModules);
        $totalCount = count($modules);

        // Ahora considera completado cualquier módulo con actividad
        $rate = ($completedCount / $totalCount) * 100;
        return round($rate, 2);
    }

    /**
     * Velocidad de completación - SIMPLIFICADA
     */
    private function calculateCompletionVelocity(array $modules, array $completedModules): float
    {
        if (empty($modules) || empty($completedModules)) {
            return 0.0;
        }

        $completedCount = count($completedModules);
        $totalCount = count($modules);

        // Velocidad como porcentaje de módulos con actividad
        return round(($completedCount / $totalCount) * 100, 2);
    }

    /**
     * Consistencia en calificaciones - MEJORADA PARA ESCALA 0-20
     */
    private function calculateGradeConsistency(array $grades): float
    {
        if (count($grades) < 2) {
            return 100.0;
        }

        $gradeValues = array_filter(array_column($grades, 'grade'), function ($grade) {
            return is_numeric($grade) && $grade >= 0 && $grade <= 20;
        });

        if (count($gradeValues) < 2) {
            return 100.0;
        }

        $average = array_sum($gradeValues) / count($gradeValues);

        // Calcular variación (en escala 0-20)
        $variations = array_map(fn($grade) => abs($grade - $average), $gradeValues);
        $averageVariation = array_sum($variations) / count($variations);

        // Convertir a puntuación de consistencia (0-100)
        // En escala 0-20, variación > 4 puntos es considerada inconsistente
        $consistencyScore = max(0, 100 - ($averageVariation * 25)); // 4 puntos = 0%

        return round($consistencyScore, 2);
    }

    /**
     * Tendencias de mejora/declive - ADAPTADA A ESCALA 0-20
     */
    private function calculateImprovementTrend(array $grades): float
    {
        if (count($grades) < 3) {
            return 0.0;
        }

        // Ordenar por fecha y filtrar calificaciones válidas
        usort($grades, function ($a, $b) {
            $timeA = strtotime($a->created_at ?? 'now');
            $timeB = strtotime($b->created_at ?? 'now');
            return $timeA - $timeB;
        });

        $validGrades = [];
        foreach ($grades as $grade) {
            if (isset($grade->grade) && is_numeric($grade->grade) && $grade->grade >= 0 && $grade->grade <= 20) {
                $validGrades[] = $grade->grade;
            }
        }

        if (count($validGrades) < 3) {
            return 0.0;
        }

        // Regresión lineal simple
        $n = count($validGrades);
        $sumX = $sumY = $sumXY = $sumX2 = 0;

        foreach ($validGrades as $i => $grade) {
            $x = $i + 1;
            $y = $grade;

            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        $denominator = ($n * $sumX2 - $sumX * $sumX);

        if ($denominator == 0) {
            return 0.0;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / $denominator;

        // Convertir pendiente a puntuación de tendencia (-100 a 100)
        // En escala 0-20, pendiente de 1 punto = 100 puntos de tendencia
        $trendScore = $slope * 100;

        return round($trendScore, 2);
    }

    /**
     * Evalúa el riesgo basado en el análisis - CRITERIOS CORREGIDOS PARA 0-20
     */
    private function assessProgressRisk(array $analysis): string
    {
        $riskScore = 0;

        // Rendimiento académico (escala 0-20)
        $averageGrade = $analysis['average_grade'];
        if ($averageGrade < 11) $riskScore += 3;      // < 11/20 = Rendimiento crítico
        elseif ($averageGrade < 14) $riskScore += 2;  // 11-13.9 = Bajo rendimiento
        elseif ($averageGrade < 16) $riskScore += 1;  // 14-15.9 = Rendimiento regular

        // Tasa de actividad (módulos con actividad vs total)
        $activityRate = $analysis['completion_rate'];
        if ($activityRate < 50) $riskScore += 3;      // Menos de la mitad de módulos con actividad
        elseif ($activityRate < 80) $riskScore += 2;  // 50-79% de actividad
        elseif ($activityRate < 100) $riskScore += 1; // 80-99% de actividad

        // Consistencia en calificaciones
        if ($analysis['grade_consistency'] < 60) $riskScore += 2;
        elseif ($analysis['grade_consistency'] < 75) $riskScore += 1;

        // Tendencia negativa fuerte
        if ($analysis['improvement_trend'] < -20) $riskScore += 2;
        elseif ($analysis['improvement_trend'] < -10) $riskScore += 1;

        // Tasa de aprobación de módulos
        $approvalRate = $analysis['module_approval_rate'] ?? 0;
        if ($approvalRate < 50) $riskScore += 2;
        elseif ($approvalRate < 70) $riskScore += 1;

        Log::info("Risk assessment for student:", [
            'average_grade' => $averageGrade,
            'activity_rate' => $activityRate,
            'approval_rate' => $approvalRate,
            'grade_consistency' => $analysis['grade_consistency'],
            'improvement_trend' => $analysis['improvement_trend'],
            'risk_score' => $riskScore
        ]);

        return match (true) {
            $riskScore >= 7 => 'critical',
            $riskScore >= 5 => 'high',
            $riskScore >= 3 => 'medium',
            default => 'low'
        };
    }

    /**
     * Calcula tendencias de progreso - ADAPTADA
     */
    private function calculateProgressTrends(array $grades): array
    {
        if (empty($grades)) return [];

        $weeklyGrades = [];
        foreach ($grades as $grade) {
            if (!isset($grade->created_at) || !isset($grade->grade)) {
                continue;
            }

            try {
                $week = Carbon::parse($grade->created_at)->format('Y-W');
                if (is_numeric($grade->grade) && $grade->grade >= 0 && $grade->grade <= 20) {
                    $weeklyGrades[$week][] = $grade->grade;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        $trends = [];
        foreach ($weeklyGrades as $week => $weekGrades) {
            $validGrades = array_filter($weekGrades, 'is_numeric');
            if (!empty($validGrades)) {
                $average = array_sum($validGrades) / count($validGrades);
                $trends[$week] = [
                    'average_grade' => round($average, 2),
                    'average_grade_normalized' => round(($average / 20) * 100, 2),
                    'count' => count($validGrades)
                ];
            }
        }

        return $trends;
    }

    /**
     * Identifica patrones de progreso - CRITERIOS ACTUALIZADOS
     */
    private function identifyProgressPatterns(array $analysis): array
    {
        $patterns = [];

        if ($analysis['grade_consistency'] < 70) {
            $patterns[] = 'calificaciones_inconsistentes';
        }

        if ($analysis['improvement_trend'] < -10) {
            $patterns[] = 'rendimiento_decreciente';
        }

        if ($analysis['completion_velocity'] < 50) {
            $patterns[] = 'progreso_lento';
        }

        if ($analysis['completion_rate'] < 70) {
            $patterns[] = 'baja_completacion';
        }

        if ($analysis['average_grade'] < 14) {
            $patterns[] = 'rendimiento_academico_bajo';
        }

        return $patterns;
    }

    /**
     * Compara con el grupo - ACTUALIZADO
     */
    private function compareWithGroup(int $groupId, array $analysis): array
    {
        try {
            $groupAvg = $this->progressRepository->getGroupProgressAverage($groupId);

            return [
                'group_average_grade' => $groupAvg['average_grade'] ?? 0,
                'group_average_normalized' => $groupAvg['average_grade_normalized'] ?? 0,
                'group_completion_rate' => $groupAvg['completion_rate'] ?? 0,
                'grade_difference' => round($analysis['average_grade'] - ($groupAvg['average_grade'] ?? 0), 2),
                'completion_difference' => round($analysis['completion_rate'] - ($groupAvg['completion_rate'] ?? 0), 2),
                'comparison_available' => true
            ];
        } catch (\Exception $e) {
            Log::warning("Error en comparación grupal: " . $e->getMessage());
            return [
                'error' => 'No se pudo obtener comparación grupal',
                'comparison_available' => false
            ];
        }
    }

    /**
     * Identifica triggers de riesgo - ACTUALIZADO
     */
    private function identifyRiskTriggers(array $analysis, string $riskLevel): array
    {
        $triggers = [];

        // Triggers académicos (escala 0-20)
        if ($analysis['average_grade'] < 11) {
            $triggers[] = 'promedio_reprobatorio';
        } elseif ($analysis['average_grade'] < 14) {
            $triggers[] = 'rendimiento_academico_bajo';
        }

        if ($analysis['completion_rate'] < 50) {
            $triggers[] = 'baja_completacion_modulos';
        } elseif ($analysis['completion_rate'] < 70) {
            $triggers[] = 'completacion_regular';
        }

        if ($analysis['grade_consistency'] < 60) {
            $triggers[] = 'inconsistencia_calificaciones';
        }

        if ($analysis['improvement_trend'] < -10) {
            $triggers[] = 'tendencia_negativa';
        }

        if ($analysis['completion_velocity'] < 50) {
            $triggers[] = 'velocidad_lenta';
        }

        return $triggers;
    }

    /**
     * Genera recomendaciones accionables - ACTUALIZADAS
     */
    private function generateRecommendations(array $analysis, string $riskLevel): array
    {
        $recommendations = [];

        // Recomendaciones basadas en rendimiento académico
        if ($analysis['average_grade'] < 11) {
            $recommendations[] = 'Necesita apoyo urgente: promedio en zona reprobatoria';
        } elseif ($analysis['average_grade'] < 14) {
            $recommendations[] = 'Reforzar contenidos con tutorías específicas';
        } elseif ($analysis['average_grade'] < 16) {
            $recommendations[] = 'Mantener rendimiento y buscar mejora continua';
        }

        // Recomendaciones basadas en completación
        if ($analysis['completion_rate'] < 50) {
            $recommendations[] = 'Plan de estudio intensivo para módulos pendientes';
        } elseif ($analysis['completion_rate'] < 70) {
            $recommendations[] = 'Organizar horarios para completar módulos atrasados';
        }

        // Recomendaciones basadas en consistencia
        if ($analysis['grade_consistency'] < 70) {
            $recommendations[] = 'Implementar rutinas de estudio consistentes';
        }

        // Recomendaciones basadas en tendencia
        if ($analysis['improvement_trend'] < -10) {
            $recommendations[] = 'Revisar estrategias de aprendizaje con docente';
        }

        if (empty($recommendations) || $analysis['average_grade'] >= 16) {
            $recommendations[] = 'Rendimiento satisfactorio - mantener excelente trabajo';
        }

        return array_slice($recommendations, 0, 3);
    }

    private function getLastActivity(array $grades, array $completedModules): ?string
    {
        $dates = [];

        foreach ($grades as $grade) {
            if (isset($grade->created_at)) {
                $dates[] = $grade->created_at;
            }
        }

        foreach ($completedModules as $module) {
            if (isset($module->completed_at)) {
                $dates[] = $module->completed_at;
            }
        }

        if (empty($dates)) return null;

        return max($dates);
    }

    private function createEmptyProgressAnalytic(int $enrollmentId, string $period): DataAnalytic
    {
        return DataAnalytic::updateOrCreate(
            [
                'analyzable_type' => 'IncadevUns\\CoreDomain\\Models\\Enrollment',
                'analyzable_id' => $enrollmentId,
                'analysis_type' => 'progress',
                'period' => $period,
            ],
            [
                'score' => 0,
                'rate' => 0,
                'total_events' => 0,
                'completed_events' => 0,
                'risk_level' => 'none',
                'metrics' => ['status' => 'no_data'],
                'trends' => [],
                'patterns' => [],
                'comparisons' => [],
                'triggers' => ['sin_datos_progreso'],
                'recommendations' => ['Recopilar datos de calificaciones y módulos'],
                'calculated_at' => now(),
            ]
        );
    }
}
