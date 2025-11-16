<?php
// app/Domains/DataAnalyst/Services/DropoutPredictionService.php

namespace App\Domains\DataAnalyst\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DropoutPredictionService
{
    protected $bigQuery;
    protected $dataset;
    protected $cacheTTL;

    public function __construct()
    {
        $this->bigQuery = new BigQueryClient([
            'projectId' => env('BIGQUERY_PROJECT_ID'),
            'keyFilePath' => base_path(env('GOOGLE_APPLICATION_CREDENTIALS')),
        ]);

        $this->dataset = $this->bigQuery->dataset('lms_analytics');
        $this->cacheTTL = 3600;
    }

    protected function executeCachedQuery(string $query, string $cacheKey, int $ttl = null): array
    {
        $ttl = $ttl ?? $this->cacheTTL;

        return Cache::remember($cacheKey, $ttl, function () use ($query) {
            $queryJobConfig = $this->bigQuery->query($query);
            $queryResults = $this->bigQuery->runQuery($queryJobConfig);
            
            $results = [];
            foreach ($queryResults as $row) {
                $results[] = $row;
            }
            
            return $results;
        });
    }

    /**
     * Obtiene predicciones de deserción - CORREGIDO con sintaxis correcta
     */
    public function getDropoutPredictions(array $filters = []): array
    {
        $whereConditions = $this->buildWhereClause($filters);

        $query = "
            SELECT
                enrollment_id,
                user_id,
                group_id,
                student_name,
                group_name,
                -- Probabilidad de abandono (0-1)
                predicted_dropped_out_probs[OFFSET(0)].prob AS dropout_probability,
                -- Predicción final (0 = No abandona, 1 = Abandona)
                predicted_dropped_out,
                -- Nivel de riesgo calculado
                CASE 
                    WHEN predicted_dropped_out_probs[OFFSET(0)].prob >= 0.7 THEN 'ALTO'
                    WHEN predicted_dropped_out_probs[OFFSET(0)].prob >= 0.4 THEN 'MEDIO' 
                    ELSE 'BAJO'
                END AS risk_level,
                -- Recomendación de acción
                CASE
                    WHEN predicted_dropped_out_probs[OFFSET(0)].prob >= 0.7 THEN 'INTERVENCIÓN INMEDIATA'
                    WHEN predicted_dropped_out_probs[OFFSET(0)].prob >= 0.4 THEN 'SEGUIMIENTO SEMANAL'
                    ELSE 'MONITOREO RUTINARIO'
                END AS recommended_action,
                -- Métricas clave
                avg_grade,
                attendance_rate,
                payment_regularity,
                total_exams_taken,
                -- Diagnóstico de datos
                CASE 
                    WHEN avg_grade = 0 AND total_exams_taken = 0 THEN 'FALTAN DATOS ACADÉMICOS'
                    WHEN attendance_rate = 0 THEN 'FALTAN DATOS ASISTENCIA'
                    ELSE 'DATOS COMPLETOS'
                END AS data_status

            FROM ML.PREDICT(
                MODEL `the-dominion-477800-m3.lms_analytics.dropout_prediction_model`,
                (SELECT * FROM `the-dominion-477800-m3.lms_analytics.dataset_prediccion`
                 WHERE 1=1 {$whereConditions})
            )
            ORDER BY dropout_probability DESC
        ";

        $cacheKey = 'dropout_predictions_' . md5(serialize($filters));
        
        try {
            $results = $this->executeCachedQuery($query, $cacheKey);
            return $this->formatPredictionResults($results);
        } catch (\Exception $e) {
            Log::error('Dropout Prediction Error: ' . $e->getMessage());
            Log::error('Query: ' . $query);
            throw $e;
        }
    }

    /**
     * Predicciones detalladas con toda la información - CORREGIDO
     */
    public function getDetailedPredictions(array $filters = []): array
    {
        $whereConditions = $this->buildWhereClause($filters);

        $query = "
            SELECT
                -- Información del estudiante
                enrollment_id,
                user_id,
                group_id,
                course_version_id,
                student_name,
                group_name,
                start_date,
                end_date,
                
                -- Métricas académicas
                avg_grade,
                grade_std_dev,
                total_exams_taken,
                grade_trend,
                max_grade,
                min_grade,
                grade_range,
                
                -- Asistencia
                attendance_rate,
                attendance_trend,
                total_sessions,
                present_count,
                recent_sessions_14d,
                exam_participation_rate,
                
                -- Financiero
                payment_regularity,
                days_since_last_payment,
                avg_payment_delay,
                total_payments,
                
                -- Progreso
                days_since_start,
                days_until_end,
                course_progress,
                sessions_progress,
                
                -- Historial
                previous_courses_completed,
                historical_avg_grade,
                avg_satisfaction_score,
                
                -- Predicción
                predicted_dropped_out as predicted_label,
                predicted_dropped_out_probs[OFFSET(0)].prob as dropout_probability,
                
                -- Análisis de riesgo
                CASE 
                    WHEN predicted_dropped_out_probs[OFFSET(0)].prob >= 0.7 THEN 'ALTO'
                    WHEN predicted_dropped_out_probs[OFFSET(0)].prob >= 0.4 THEN 'MEDIO'
                    ELSE 'BAJO'
                END as risk_level,

                -- Recomendaciones basadas en el riesgo
                CASE 
                    WHEN predicted_dropped_out_probs[OFFSET(0)].prob >= 0.7 THEN 
                        'Intervención inmediata requerida. Contactar al estudiante y asignar tutor.'
                    WHEN predicted_dropped_out_probs[OFFSET(0)].prob >= 0.4 THEN 
                        'Monitoreo cercano. Programar reunión de seguimiento.'
                    ELSE 'Rendimiento normal. Continuar monitoreo estándar.'
                END as recommendation,

                -- Factores de riesgo identificados
                CASE WHEN attendance_rate < 70 THEN 'Asistencia baja' ELSE NULL END as risk_factor_1,
                CASE WHEN avg_grade < 60 THEN 'Rendimiento bajo' ELSE NULL END as risk_factor_2,
                CASE WHEN payment_regularity < 0.5 THEN 'Pagos irregulares' ELSE NULL END as risk_factor_3,
                CASE WHEN days_since_last_payment > 30 THEN 'Último pago hace más de 30 días' ELSE NULL END as risk_factor_4

            FROM ML.PREDICT(
                MODEL `the-dominion-477800-m3.lms_analytics.dropout_prediction_model`,
                (SELECT * FROM `the-dominion-477800-m3.lms_analytics.dataset_prediccion`
                 WHERE 1=1 {$whereConditions})
            )
            ORDER BY dropout_probability DESC
        ";

        $cacheKey = 'detailed_predictions_' . md5(serialize($filters));
        
        try {
            $results = $this->executeCachedQuery($query, $cacheKey);
            return $this->formatDetailedResults($results);
        } catch (\Exception $e) {
            Log::error('Detailed Prediction Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Estudiantes de alto riesgo (probabilidad > 70%) - CORREGIDO
     */
    public function getHighRiskStudents(): array
    {
        $query = "
            SELECT
                enrollment_id,
                user_id,
                group_id,
                student_name,
                group_name,
                -- Probabilidad formateada como porcentaje
                ROUND(predicted_dropped_out_probs[OFFSET(0)].prob * 100, 1) as riesgo_porcentaje,
                predicted_dropped_out_probs[OFFSET(0)].prob as dropout_probability,
                avg_grade,
                attendance_rate,
                payment_regularity,
                days_since_last_payment,
                -- Acción recomendada
                CASE
                    WHEN predicted_dropped_out_probs[OFFSET(0)].prob >= 0.7 THEN '🚨 INTERVENCIÓN INMEDIATA'
                    WHEN predicted_dropped_out_probs[OFFSET(0)].prob >= 0.5 THEN '⚠️ SEGUIMIENTO SEMANAL' 
                    ELSE '📊 MONITOREO MENSUAL'
                END as accion_recomendada

            FROM ML.PREDICT(
                MODEL `the-dominion-477800-m3.lms_analytics.dropout_prediction_model`,
                (SELECT * FROM `the-dominion-477800-m3.lms_analytics.dataset_prediccion`)
            )
            WHERE predicted_dropped_out_probs[OFFSET(0)].prob >= 0.7
            ORDER BY dropout_probability DESC
        ";

        try {
            $results = $this->executeCachedQuery($query, 'high_risk_students');
            return $results;
        } catch (\Exception $e) {
            Log::error('High Risk Students Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Predicciones por grupo específico - CORREGIDO
     */
    public function getPredictionsByGroup(int $groupId): array
    {
        $query = "
            SELECT
                enrollment_id,
                user_id,
                group_id,
                student_name,
                group_name,
                ROUND(predicted_dropped_out_probs[OFFSET(0)].prob * 100, 1) as riesgo_porcentaje,
                predicted_dropped_out_probs[OFFSET(0)].prob as dropout_probability,
                CASE 
                    WHEN predicted_dropped_out_probs[OFFSET(0)].prob >= 0.7 THEN 'ALTO RIESGO'
                    WHEN predicted_dropped_out_probs[OFFSET(0)].prob >= 0.4 THEN 'RIESGO MEDIO' 
                    ELSE 'BAJO RIESGO'
                END AS risk_level,
                avg_grade,
                attendance_rate,
                payment_regularity

            FROM ML.PREDICT(
                MODEL `the-dominion-477800-m3.lms_analytics.dropout_prediction_model`,
                (SELECT * FROM `the-dominion-477800-m3.lms_analytics.dataset_prediccion`
                 WHERE group_id = {$groupId})
            )
            ORDER BY dropout_probability DESC
        ";

        $cacheKey = 'group_predictions_' . $groupId;
        
        try {
            $results = $this->executeCachedQuery($query, $cacheKey);
            return $this->formatPredictionResults($results);
        } catch (\Exception $e) {
            Log::error('Group Predictions Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene métricas de evaluación del modelo existente
     */
    public function getModelMetrics(): array
    {
        $query = "
            SELECT
                *
            FROM ML.EVALUATE(MODEL `the-dominion-477800-m3.lms_analytics.dropout_prediction_model`)
        ";

        try {
            $results = $this->executeCachedQuery($query, 'model_metrics');
            return $results[0] ?? [];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Construye cláusula WHERE para filtros - CORREGIDO
     */
    private function buildWhereClause(array $filters): string
    {
        $conditions = [];

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'group_id':
                    $conditions[] = "group_id = " . (int)$value;
                    break;
                case 'course_version_id':
                    $conditions[] = "course_version_id = " . (int)$value;
                    break;
                case 'min_risk_level':
                    // Este filtro se aplica después de la predicción
                    break;
                case 'only_with_data':
                    $conditions[] = "avg_grade > 0 AND attendance_rate > 0";
                    break;
            }
        }

        return $conditions ? ' AND ' . implode(' AND ', $conditions) : '';
    }

    /**
     * Formatea resultados de predicción
     */
    private function formatPredictionResults(array $results): array
    {
        $formatted = [
            'predictions' => $results,
            'summary' => [
                'total_students' => count($results),
                'high_risk_count' => 0,
                'medium_risk_count' => 0,
                'low_risk_count' => 0,
                'avg_dropout_probability' => 0,
                'data_status_summary' => [
                    'complete_data' => 0,
                    'missing_academic' => 0,
                    'missing_attendance' => 0
                ]
            ]
        ];

        if (!empty($results)) {
            $probabilities = array_column($results, 'dropout_probability');
            $formatted['summary']['avg_dropout_probability'] = round(array_sum($probabilities) / count($probabilities), 4);
            
            $riskLevels = array_column($results, 'risk_level');
            $formatted['summary']['high_risk_count'] = count(array_filter($riskLevels, fn($level) => $level === 'ALTO'));
            $formatted['summary']['medium_risk_count'] = count(array_filter($riskLevels, fn($level) => $level === 'MEDIO'));
            $formatted['summary']['low_risk_count'] = count(array_filter($riskLevels, fn($level) => $level === 'BAJO'));

            // Resumen de estado de datos
            $dataStatus = array_column($results, 'data_status');
            $formatted['summary']['data_status_summary']['complete_data'] = count(array_filter($dataStatus, fn($status) => $status === 'DATOS COMPLETOS'));
            $formatted['summary']['data_status_summary']['missing_academic'] = count(array_filter($dataStatus, fn($status) => $status === 'FALTAN DATOS ACADÉMICOS'));
            $formatted['summary']['data_status_summary']['missing_attendance'] = count(array_filter($dataStatus, fn($status) => $status === 'FALTAN DATOS ASISTENCIA'));
        }

        return $formatted;
    }

    /**
     * Formatea resultados detallados
     */
    private function formatDetailedResults(array $results): array
    {
        return [
            'students' => $results,
            'analysis' => [
                'total' => count($results),
                'risk_distribution' => $this->calculateRiskDistribution($results),
                'performance_insights' => $this->generatePerformanceInsights($results),
                'common_risk_factors' => $this->analyzeRiskFactors($results)
            ]
        ];
    }

    private function calculateRiskDistribution(array $students): array
    {
        $distribution = [
            'ALTO' => 0,
            'MEDIO' => 0,
            'BAJO' => 0
        ];

        foreach ($students as $student) {
            $distribution[$student['risk_level']]++;
        }

        return $distribution;
    }

    private function generatePerformanceInsights(array $students): array
    {
        if (empty($students)) {
            return [];
        }

        $highRiskStudents = array_filter($students, fn($s) => $s['risk_level'] === 'ALTO');
        
        $insights = [
            'common_issues' => [],
            'avg_metrics_high_risk' => [
                'attendance_rate' => 0,
                'avg_grade' => 0,
                'payment_regularity' => 0
            ]
        ];

        if (!empty($highRiskStudents)) {
            $insights['avg_metrics_high_risk']['attendance_rate'] = round(
                array_sum(array_column($highRiskStudents, 'attendance_rate')) / count($highRiskStudents), 2
            );
            $insights['avg_metrics_high_risk']['avg_grade'] = round(
                array_sum(array_column($highRiskStudents, 'avg_grade')) / count($highRiskStudents), 2
            );
            $insights['avg_metrics_high_risk']['payment_regularity'] = round(
                array_sum(array_column($highRiskStudents, 'payment_regularity')) / count($highRiskStudents), 2
            );
        }

        // Identificar problemas comunes
        $lowAttendance = count(array_filter($students, fn($s) => $s['attendance_rate'] < 70));
        $lowGrades = count(array_filter($students, fn($s) => $s['avg_grade'] < 60));
        $paymentIssues = count(array_filter($students, fn($s) => $s['payment_regularity'] < 0.5));

        $insights['common_issues'] = [
            'low_attendance' => $lowAttendance,
            'low_grades' => $lowGrades,
            'payment_issues' => $paymentIssues
        ];

        return $insights;
    }

    private function analyzeRiskFactors(array $students): array
    {
        $factors = [
            'attendance' => 0,
            'academic' => 0,
            'financial' => 0,
            'payment_delay' => 0
        ];

        foreach ($students as $student) {
            if ($student['attendance_rate'] < 70) $factors['attendance']++;
            if ($student['avg_grade'] < 60) $factors['academic']++;
            if ($student['payment_regularity'] < 0.5) $factors['financial']++;
            if ($student['days_since_last_payment'] > 30) $factors['payment_delay']++;
        }

        return $factors;
    }
}