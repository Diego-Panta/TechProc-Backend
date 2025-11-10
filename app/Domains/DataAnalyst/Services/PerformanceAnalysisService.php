<?php
// app/Domains/DataAnalyst/Services/PerformanceAnalysisService.php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Models\DataAnalytic;
use App\Domains\DataAnalyst\Repositories\PerformanceDataRepository;
use Illuminate\Support\Facades\Log;

class PerformanceAnalysisService
{
    public function __construct(
        private PerformanceDataRepository $performanceRepository
    ) {}

    /**
     * Analiza el rendimiento de un grupo
     */
    public function analyzeGroupPerformance(int $groupId, string $period = '30d'): ?DataAnalytic
    {
        try {
            $group = $this->performanceRepository->getGroup($groupId);

            if (!$group) {
                throw new \Exception("Grupo no encontrado: {$groupId}");
            }

            $performanceData = $this->performanceRepository->getGroupPerformanceData($groupId, $period);

            // DEBUG
            Log::info("=== PERFORMANCE ANALYSIS DEBUG ===");
            Log::info("Group: {$groupId}, Period: {$period}");
            Log::info("Basic metrics: " . json_encode($performanceData['basic_metrics'] ?? []));
            Log::info("Grade distribution count: " . count($performanceData['grade_distribution'] ?? []));
            Log::info("Instructors count: " . count($performanceData['instructor_effectiveness'] ?? []));
            Log::info("Modules count: " . count($performanceData['module_performance'] ?? []));

            if (empty($performanceData['basic_metrics'])) {
                Log::warning("No data found for group {$groupId}");
                return $this->createEmptyPerformanceAnalytic($groupId, $period);
            }

            $analysis = $this->calculatePerformanceAnalysis($performanceData);
            $riskLevel = $this->assessPerformanceRisk($analysis);

            Log::info("Final performance analysis for group {$groupId}:", [
                'overall_score' => $analysis['overall_score'],
                'average_grade' => $analysis['average_grade'],
                'completion_rate' => $analysis['completion_rate'],
                'risk_level' => $riskLevel
            ]);

            return DataAnalytic::updateOrCreate(
                [
                    'analyzable_type' => 'IncadevUns\\CoreDomain\\Models\\Group',
                    'analyzable_id' => $groupId,
                    'analysis_type' => 'performance',
                    'period' => $period,
                ],
                [
                    'score' => $analysis['overall_score'],
                    'rate' => $analysis['completion_rate'],
                    'total_events' => $analysis['total_students'],
                    'completed_events' => $analysis['active_students'],
                    'risk_level' => $riskLevel,
                    'metrics' => $analysis,
                    'trends' => $this->calculatePerformanceTrends($performanceData),
                    'patterns' => $this->identifyPerformancePatterns($analysis),
                    'comparisons' => $this->compareWithInstitutionalAverage($analysis),
                    'triggers' => $this->identifyPerformanceTriggers($analysis, $riskLevel),
                    'recommendations' => $this->generatePerformanceRecommendations($analysis, $riskLevel),
                    'calculated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error("Error analizando rendimiento grupo {$groupId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calcula el análisis de rendimiento principal
     */
    private function calculatePerformanceAnalysis(array $performanceData): array
    {
        $basicMetrics = $performanceData['basic_metrics'] ?? [];
        $gradeDistribution = $performanceData['grade_distribution'] ?? [];
        $instructorEffectiveness = $performanceData['instructor_effectiveness'] ?? [];
        $modulePerformance = $performanceData['module_performance'] ?? [];

        // Métricas base
        $totalStudents = $basicMetrics['total_students'] ?? 0;
        $activeStudents = $basicMetrics['active_students'] ?? 0;
        $averageGrade = $basicMetrics['average_grade'] ?? 0;
        $averageAttendance = $basicMetrics['average_attendance'] ?? 0;
        $passedStudents = $basicMetrics['passed_students'] ?? 0;

        // Cálculos derivados
        $completionRate = $totalStudents > 0 ? ($passedStudents / $totalStudents) * 100 : 0;
        $activityRate = $totalStudents > 0 ? ($activeStudents / $totalStudents) * 100 : 0;

        // Calcular score general (0-100)
        $overallScore = $this->calculateOverallScore(
            $averageGrade,
            $completionRate,
            $averageAttendance,
            $gradeDistribution
        );

        // Efectividad de instructores promedio
        $instructorEffectivenessScore = $this->calculateInstructorEffectiveness($instructorEffectiveness);

        // Salud de módulos
        $moduleHealthScore = $this->calculateModuleHealth($modulePerformance);

        return [
            // Métricas principales
            'overall_score' => round($overallScore, 2),
            'average_grade' => round($averageGrade, 2),
            'completion_rate' => round($completionRate, 2),
            'attendance_rate' => round($averageAttendance, 2),
            'activity_rate' => round($activityRate, 2),

            // Estadísticas de estudiantes
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'passed_students' => $passedStudents,

            // Distribución
            'grade_distribution' => $gradeDistribution,
            'high_performers_rate' => $this->calculateHighPerformersRate($gradeDistribution),

            // Efectividad
            'instructor_effectiveness_score' => $instructorEffectivenessScore,
            'module_health_score' => $moduleHealthScore,

            // Actividad
            'total_sessions' => $basicMetrics['total_sessions'] ?? 0,
            'total_exams' => $basicMetrics['total_exams'] ?? 0,
        ];
    }

    /**
     * Calcula score general usando fórmula ponderada
     */
    private function calculateOverallScore(
        float $averageGrade,
        float $completionRate,
        float $attendanceRate,
        array $gradeDistribution
    ): float {
        // Convertir calificación promedio a escala 0-100
        $gradeScore = ($averageGrade / 20) * 100;

        // Ponderaciones
        $weights = [
            'grade' => 0.4,        // 40% calificaciones
            'completion' => 0.3,   // 30% tasa de completación
            'attendance' => 0.2,   // 20% asistencia
            'distribution' => 0.1  // 10% distribución de calificaciones
        ];

        // Calcular score de distribución (bonus por buenas calificaciones)
        $distributionScore = $this->calculateDistributionScore($gradeDistribution);

        $overallScore =
            ($gradeScore * $weights['grade']) +
            ($completionRate * $weights['completion']) +
            ($attendanceRate * $weights['attendance']) +
            ($distributionScore * $weights['distribution']);

        return min(100, max(0, $overallScore));
    }

    /**
     * Calcula score basado en distribución de calificaciones
     */
    private function calculateDistributionScore(array $gradeDistribution): float
    {
        if (empty($gradeDistribution)) {
            return 0;
        }

        $highGrades = 0;
        $totalStudents = 0;

        foreach ($gradeDistribution as $distribution) {
            $studentCount = $distribution->student_count ?? 0;
            $totalStudents += $studentCount;

            $gradeRange = $distribution->grade_range ?? '';

            // VERSIÓN CON MATCH: Más limpia y explícita
            if ($this->getGradeCategoryLevel($gradeRange) === 'high') {
                $highGrades += $studentCount;
            }
        }

        if ($totalStudents === 0) {
            return 0;
        }

        return ($highGrades / $totalStudents) * 100;
    }

    /**
     * Clasifica el nivel de rendimiento basado en el rango de calificación
     */
    private function getGradeCategoryLevel(string $gradeRange): string
    {
        return match (true) {
            str_contains($gradeRange, 'A (18-20)') => 'high',
            str_contains($gradeRange, 'B (16-17.9)') => 'high',
            str_contains($gradeRange, 'C (14-15.9)') => 'medium',
            str_contains($gradeRange, 'D (11-13.9)') => 'low',
            str_contains($gradeRange, 'F (0-10.9)') => 'critical',
            default => 'unknown'
        };
    }

    /**
     * Calcula tasa de estudiantes de alto rendimiento - CORREGIDO
     */
    private function calculateHighPerformersRate(array $gradeDistribution): float
    {
        return $this->calculateDistributionScore($gradeDistribution);
    }

    /**
     * Calcula efectividad de instructores
     */
    private function calculateInstructorEffectiveness(array $instructors): float
    {
        if (empty($instructors)) {
            return 0;
        }

        $totalScore = 0;
        $count = 0;

        foreach ($instructors as $instructor) {
            $gradeScore = ($instructor->average_student_grade ?? 0) > 0 ?
                (($instructor->average_student_grade / 20) * 100) : 0;

            $ratingScore = $instructor->average_rating ?? 0;

            // Combinar scores (70% calificaciones, 30% ratings)
            $instructorScore = ($gradeScore * 0.7) + ($ratingScore * 0.3);
            $totalScore += $instructorScore;
            $count++;
        }

        return $count > 0 ? round($totalScore / $count, 2) : 0;
    }

    /**
     * Calcula salud de módulos
     */
    private function calculateModuleHealth(array $modules): float
    {
        if (empty($modules)) {
            return 0;
        }

        $totalScore = 0;
        $count = 0;

        foreach ($modules as $module) {
            $gradeScore = ($module->average_grade ?? 0) > 0 ?
                (($module->average_grade / 20) * 100) : 0;

            $evaluationScore = $module->evaluation_rate ?? 0;

            // Combinar scores
            $moduleScore = ($gradeScore * 0.6) + ($evaluationScore * 0.4);
            $totalScore += $moduleScore;
            $count++;
        }

        return $count > 0 ? round($totalScore / $count, 2) : 0;
    }

    /**
     * Evalúa el riesgo basado en el análisis
     */
    private function assessPerformanceRisk(array $analysis): string
    {
        $riskScore = 0;

        // Score general
        if ($analysis['overall_score'] < 60) $riskScore += 3;
        elseif ($analysis['overall_score'] < 70) $riskScore += 2;
        elseif ($analysis['overall_score'] < 80) $riskScore += 1;

        // Calificación promedio (escala 0-20)
        if ($analysis['average_grade'] < 11) $riskScore += 3;
        elseif ($analysis['average_grade'] < 14) $riskScore += 2;
        elseif ($analysis['average_grade'] < 16) $riskScore += 1;

        // Tasa de completación
        if ($analysis['completion_rate'] < 50) $riskScore += 3;
        elseif ($analysis['completion_rate'] < 60) $riskScore += 2;
        elseif ($analysis['completion_rate'] < 70) $riskScore += 1;

        // Tasa de actividad
        if ($analysis['activity_rate'] < 60) $riskScore += 2;
        elseif ($analysis['activity_rate'] < 80) $riskScore += 1;

        // Efectividad de instructores
        if ($analysis['instructor_effectiveness_score'] < 60) $riskScore += 2;
        elseif ($analysis['instructor_effectiveness_score'] < 70) $riskScore += 1;

        return match (true) {
            $riskScore >= 8 => 'critical',
            $riskScore >= 6 => 'high',
            $riskScore >= 4 => 'medium',
            default => 'low'
        };
    }

    /**
     * Métodos auxiliares para las funciones específicas
     */
    public function getGradeDistribution(int $groupId): array
    {
        return $this->performanceRepository->getGradeDistributionData($groupId);
    }

    public function getInstructorEffectiveness(int $groupId): array
    {
        return $this->performanceRepository->getInstructorEffectivenessData($groupId);
    }

    public function getModulePerformance(int $groupId): array
    {
        return $this->performanceRepository->getModulePerformanceData($groupId);
    }

    /**
     * Métodos de soporte (similares a ProgressAnalysisService)
     */
    private function calculatePerformanceTrends(array $performanceData): array
    {
        // Implementar lógica de tendencias si hay datos históricos
        return [
            'grade_trend' => 'stable',
            'completion_trend' => 'stable',
            'attendance_trend' => 'stable'
        ];
    }

    private function identifyPerformancePatterns(array $analysis): array
    {
        $patterns = [];

        if ($analysis['overall_score'] < 70) {
            $patterns[] = 'rendimiento_general_bajo';
        }

        if ($analysis['average_grade'] < 14) {
            $patterns[] = 'calificaciones_bajas';
        }

        if ($analysis['completion_rate'] < 60) {
            $patterns[] = 'baja_tasa_aprobacion';
        }

        if ($analysis['high_performers_rate'] < 20) {
            $patterns[] = 'pocos_estudiantes_destacados';
        }

        if ($analysis['instructor_effectiveness_score'] < 70) {
            $patterns[] = 'efectividad_instructores_baja';
        }

        return $patterns;
    }

    private function compareWithInstitutionalAverage(array $analysis): array
    {
        $institutionalAvg = $this->performanceRepository->getInstitutionalAverage();

        return [
            'institutional_average' => $institutionalAvg,
            'grade_difference' => round($analysis['average_grade'] - $institutionalAvg['average_grade'], 2),
            'completion_difference' => round($analysis['completion_rate'] - $institutionalAvg['completion_rate'], 2),
            'attendance_difference' => round($analysis['attendance_rate'] - $institutionalAvg['attendance_rate'], 2),
            'comparison_available' => true
        ];
    }

    private function identifyPerformanceTriggers(array $analysis, string $riskLevel): array
    {
        $triggers = [];

        if ($analysis['overall_score'] < 70) {
            $triggers[] = 'score_general_bajo';
        }

        if ($analysis['average_grade'] < 14) {
            $triggers[] = 'promedio_calificaciones_bajo';
        }

        if ($analysis['completion_rate'] < 60) {
            $triggers[] = 'tasa_aprobacion_baja';
        }

        if ($analysis['activity_rate'] < 80) {
            $triggers[] = 'baja_actividad_estudiantes';
        }

        return $triggers;
    }

    private function generatePerformanceRecommendations(array $analysis, string $riskLevel): array
    {
        $recommendations = [];

        if ($analysis['overall_score'] < 70) {
            $recommendations[] = 'Implementar plan de mejora académica integral';
        }

        if ($analysis['average_grade'] < 14) {
            $recommendations[] = 'Reforzar contenidos con sesiones de recuperación';
        }

        if ($analysis['completion_rate'] < 60) {
            $recommendations[] = 'Revisar estrategias de evaluación y apoyo estudiantil';
        }

        if ($analysis['instructor_effectiveness_score'] < 70) {
            $recommendations[] = 'Capacitación y apoyo para instructores';
        }

        if ($analysis['high_performers_rate'] < 20) {
            $recommendations[] = 'Implementar programas de excelencia académica';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Rendimiento satisfactorio - mantener estrategias actuales';
        }

        return array_slice($recommendations, 0, 3);
    }

    private function createEmptyPerformanceAnalytic(int $groupId, string $period): DataAnalytic
    {
        return DataAnalytic::updateOrCreate(
            [
                'analyzable_type' => 'IncadevUns\\CoreDomain\\Models\\Group',
                'analyzable_id' => $groupId,
                'analysis_type' => 'performance',
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
                'triggers' => ['sin_datos_rendimiento'],
                'recommendations' => ['Recopilar datos del grupo y estudiantes'],
                'calculated_at' => now(),
            ]
        );
    }
}
