<?php
// app/Domains/DataAnalyst/Services/BaseAnalyticsService.php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Models\DataAnalytic;
use Illuminate\Database\Eloquent\Model;

class BaseAnalyticsService
{
    /**
     * Guarda o actualiza el análisis en la tabla polimórfica
     */
    protected function saveAnalysis(
        Model $analyzable,
        string $analysisType,
        string $period,
        array $data
    ): DataAnalytic {
        return DataAnalytic::updateOrCreate(
            [
                'analyzable_type' => get_class($analyzable),
                'analyzable_id' => $analyzable->id,
                'analysis_type' => $analysisType,
                'period' => $period,
            ],
            array_merge($data, [
                'calculated_at' => now(),
            ])
        );
    }

    /**
     * Obtiene el análisis existente o calcula uno nuevo
     */
    protected function getOrCalculateAnalysis(
        Model $analyzable,
        string $analysisType,
        string $period,
        bool $refresh = false
    ): ?DataAnalytic {
        if (!$refresh) {
            $existing = DataAnalytic::where([
                'analyzable_type' => get_class($analyzable),
                'analyzable_id' => $analyzable->id,
                'analysis_type' => $analysisType,
                'period' => $period,
            ])->first();

            if ($existing) {
                return $existing;
            }
        }

        // Si no existe o se solicita refresh, calcular nuevo
        return $this->calculateAnalysis($analyzable, $analysisType, $period);
    }

    /**
     * Método abstracto para cálculo específico (debe implementarse en clases hijas)
     */
    protected function calculateAnalysis(Model $analyzable, string $analysisType, string $period): ?DataAnalytic
    {
        // Implementar en clases específicas
        return null;
    }

    /**
     * Formatea datos para análisis de asistencia
     */
    protected function formatAttendanceData(array $analysis): array
    {
        return [
            'score' => $analysis['attendance_rate'] ?? null,
            'rate' => $analysis['attendance_rate'] ?? null,
            'total_events' => $analysis['total_sessions'] ?? null,
            'completed_events' => $analysis['attended_sessions'] ?? null,
            'risk_level' => $analysis['risk_assessment']['risk_level'] ?? null,
            'metrics' => [
                'absent_sessions' => $analysis['absent_sessions'] ?? null,
                'late_sessions' => $analysis['late_sessions'] ?? null,
                'consecutive_absences' => $analysis['max_consecutive_absences'] ?? null,
                'last_attendance_date' => $analysis['last_attendance_date'] ?? null,
            ],
            'trends' => [
                'attendance_trend' => $analysis['attendance_trend'] ?? null,
                'consistency_score' => $analysis['patterns']['consistency']['consistency_score'] ?? null,
            ],
            'patterns' => $analysis['patterns'] ?? null,
            'comparisons' => $analysis['comparison'] ?? null,
            'triggers' => $analysis['risk_assessment']['triggers'] ?? null,
            'recommendations' => $analysis['recommendations'] ?? null,
        ];
    }

    /**
     * Formatea datos para análisis de riesgo
     */
    protected function formatRiskData(array $riskAssessment): array
    {
        return [
            'score' => $riskAssessment['risk_score'] ?? null,
            'risk_level' => $riskAssessment['risk_level'] ?? null,
            'metrics' => [
                'academic_score' => $riskAssessment['component_scores']['academic'] ?? null,
                'attendance_score' => $riskAssessment['component_scores']['attendance'] ?? null,
                'financial_score' => $riskAssessment['component_scores']['financial'] ?? null,
                'engagement_score' => $riskAssessment['component_scores']['engagement'] ?? null,
                'behavioral_score' => $riskAssessment['component_scores']['behavioral'] ?? null,
            ],
            'triggers' => $riskAssessment['triggers'] ?? null,
            'recommendations' => $riskAssessment['recommendations'] ?? null,
        ];
    }

    /**
     * Formatea datos para análisis de progreso
     */
    protected function formatProgressData(array $progressAnalysis): array
    {
        return [
            'score' => $progressAnalysis['progress_score'] ?? null,
            'rate' => $progressAnalysis['completion_rate'] ?? null,
            'total_events' => $progressAnalysis['total_modules'] ?? null,
            'completed_events' => $progressAnalysis['completed_modules'] ?? null,
            'risk_level' => $progressAnalysis['risk_level'] ?? null,
            'metrics' => [
                'average_grade' => $progressAnalysis['average_grade'] ?? null,
                'grade_trend' => $progressAnalysis['grade_trend'] ?? null,
                'modules_behind' => $progressAnalysis['modules_behind'] ?? null,
            ],
            'trends' => $progressAnalysis['trends'] ?? null,
            'patterns' => $progressAnalysis['patterns'] ?? null,
            'triggers' => $progressAnalysis['triggers'] ?? null,
            'recommendations' => $progressAnalysis['recommendations'] ?? null,
        ];
    }

    /**
     * Formatea datos para análisis de rendimiento
     */
    protected function formatPerformanceData(array $performanceAnalysis): array
    {
        return [
            'score' => $performanceAnalysis['performance_score'] ?? null,
            'rate' => $performanceAnalysis['success_rate'] ?? null,
            'total_events' => $performanceAnalysis['total_exams'] ?? null,
            'completed_events' => $performanceAnalysis['passed_exams'] ?? null,
            'risk_level' => $performanceAnalysis['risk_level'] ?? null,
            'metrics' => [
                'average_grade' => $performanceAnalysis['average_grade'] ?? null,
                'grade_distribution' => $performanceAnalysis['grade_distribution'] ?? null,
                'improvement_rate' => $performanceAnalysis['improvement_rate'] ?? null,
            ],
            'trends' => $performanceAnalysis['trends'] ?? null,
            'comparisons' => $performanceAnalysis['comparisons'] ?? null,
            'triggers' => $performanceAnalysis['triggers'] ?? null,
            'recommendations' => $performanceAnalysis['recommendations'] ?? null,
        ];
    }
}