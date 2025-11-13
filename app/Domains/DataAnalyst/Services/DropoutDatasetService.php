<?php
// app/Domains/DataAnalyst/Services/DropoutDatasetService.php

namespace App\Domains\DataAnalyst\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DropoutDatasetService
{
    protected $bigQuery;
    protected $dataset;

    public function __construct()
    {
        $this->bigQuery = new BigQueryClient([
            'projectId' => env('BIGQUERY_PROJECT_ID'),
            'keyFilePath' => base_path(env('GOOGLE_APPLICATION_CREDENTIALS')),
        ]);

        $this->dataset = $this->bigQuery->dataset('lms_analytics');
    }

    /**
     * Dataset para entrenamiento con datos históricos simulados
     */
    public function generateTrainingDataset(): array
    {
        $query = "
            -- Crear datos históricos simulados basados en datos actuales
            WITH historical_data AS (
                SELECT 
                    e.id as enrollment_id,
                    e.user_id,
                    e.group_id,
                    g.name as group_name,
                    
                    -- Simular fechas históricas (grupos que ya terminaron)
                    DATE_SUB(g.start_date, INTERVAL 6 MONTH) as historical_start_date,
                    DATE_SUB(g.end_date, INTERVAL 6 MONTH) as historical_end_date,
                    
                    -- Características académicas (basadas en datos reales)
                    AVG(gr.grade) as avg_grade,
                    COUNT(gr.id) as total_exams_taken,
                    MAX(gr.grade) as max_grade,
                    MIN(gr.grade) as min_grade,
                    
                    -- Características de asistencia
                    COUNT(a.id) as total_sessions,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SAFE_DIVIDE(
                        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END), 
                        COUNT(a.id)
                    ) * 100 as attendance_rate,
                    
                    -- Características de pagos
                    COUNT(ep.id) as total_payments,
                    SUM(CASE WHEN ep.status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
                    
                    -- Características de soporte
                    COUNT(DISTINCT t.id) as total_tickets,
                    
                    -- Simular estado académico basado en rendimiento
                    CASE 
                        WHEN AVG(gr.grade) < 8 OR 
                             SAFE_DIVIDE(SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END), COUNT(a.id)) * 100 < 50
                        THEN 'dropped'
                        WHEN AVG(gr.grade) < 11
                        THEN 'failed' 
                        ELSE 'active'
                    END as simulated_status,
                    
                    -- Variable objetivo basada en estado simulado
                    CASE 
                        WHEN AVG(gr.grade) < 8 OR 
                             SAFE_DIVIDE(SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END), COUNT(a.id)) * 100 < 50
                        THEN 1
                        WHEN AVG(gr.grade) < 11
                        THEN 1
                        ELSE 0
                    END as dropped_out

                FROM `lms_analytics.enrollments` e
                JOIN `lms_analytics.groups` g ON e.group_id = g.id
                LEFT JOIN `lms_analytics.attendances` a ON e.id = a.enrollment_id
                LEFT JOIN `lms_analytics.grades` gr ON e.id = gr.enrollment_id
                LEFT JOIN `lms_analytics.enrollment_payments` ep ON e.id = ep.enrollment_id
                LEFT JOIN `lms_analytics.tickets` t ON e.user_id = t.user_id
                
                WHERE e.academic_status = 'active'  -- Usar estudiantes activos actuales
                GROUP BY e.id, e.user_id, e.group_id, g.name, g.start_date, g.end_date
                HAVING total_sessions > 0 AND total_exams_taken > 0  -- Solo estudiantes con actividad
            )
            
            SELECT 
                enrollment_id,
                user_id,
                group_id,
                group_name,
                
                -- Características académicas
                COALESCE(avg_grade, 0) as avg_grade,
                COALESCE(total_exams_taken, 0) as total_exams_taken,
                COALESCE(max_grade, 0) as max_grade,
                COALESCE(min_grade, 0) as min_grade,
                COALESCE(max_grade - min_grade, 0) as grade_range,
                
                -- Características de asistencia
                COALESCE(total_sessions, 0) as total_sessions,
                COALESCE(present_count, 0) as present_count,
                COALESCE(total_sessions - present_count, 0) as absent_count,
                COALESCE(attendance_rate, 0) as attendance_rate,
                
                -- Características de pagos
                COALESCE(total_payments, 0) as total_payments,
                COALESCE(pending_payments, 0) as pending_payments,
                
                -- Características de soporte
                COALESCE(total_tickets, 0) as total_tickets,
                
                -- Indicadores de riesgo
                CASE WHEN avg_grade < 11 THEN 1 ELSE 0 END as low_performance_flag,
                CASE WHEN attendance_rate < 70 THEN 1 ELSE 0 END as low_attendance_flag,
                CASE WHEN pending_payments > 0 THEN 1 ELSE 0 END as pending_payments_flag,
                CASE WHEN total_tickets > 2 THEN 1 ELSE 0 END as many_tickets_flag,
                
                -- Variable objetivo
                dropped_out,
                
                -- Metadatos para referencia
                simulated_status,
                historical_start_date as start_date,
                historical_end_date as end_date
                
            FROM historical_data
            ORDER BY enrollment_id
        ";

        try {
            $queryJobConfig = $this->bigQuery->query($query);
            $queryResults = $this->bigQuery->runQuery($queryJobConfig);
            
            $results = [];
            foreach ($queryResults as $row) {
                $results[] = $row;
            }
            
            return $results;
        } catch (\Exception $e) {
            Log::error('Training Dataset Error: ' . $e->getMessage());
            throw new \Exception("Error generando dataset de entrenamiento: " . $e->getMessage());
        }
    }

    /**
     * Dataset para predicción en tiempo real
     */
    public function generatePredictionDataset(): array
    {
        $query = "
            SELECT 
                e.id as enrollment_id,
                e.user_id,
                e.group_id,
                g.name as group_name,
                u.name as student_name,
                
                -- Características académicas
                AVG(gr.grade) as avg_grade,
                COUNT(gr.id) as total_exams_taken,
                MAX(gr.grade) as max_grade,
                MIN(gr.grade) as min_grade,
                (MAX(gr.grade) - MIN(gr.grade)) as grade_range,
                
                -- Características de asistencia
                COUNT(a.id) as total_sessions,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                SAFE_DIVIDE(
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END), 
                    COUNT(a.id)
                ) * 100 as attendance_rate,
                
                -- Asistencia reciente (últimas 2 semanas)
                COUNT(CASE WHEN DATE(a.created_at) >= DATE_SUB(CURRENT_DATE(), INTERVAL 14 DAY) 
                           THEN a.id END) as recent_sessions_14d,
                
                -- Características de pagos
                COUNT(ep.id) as total_payments,
                SUM(CASE WHEN ep.status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
                SUM(CASE WHEN ep.status = 'rejected' THEN 1 ELSE 0 END) as rejected_payments,
                
                -- Características de soporte
                COUNT(DISTINCT t.id) as total_tickets,
                COUNT(DISTINCT CASE WHEN t.status = 'open' THEN t.id END) as open_tickets,
                
                -- Características temporales
                DATE_DIFF(CURRENT_DATE(), DATE(g.start_date), DAY) as days_since_start,
                DATE_DIFF(DATE(g.end_date), CURRENT_DATE(), DAY) as days_until_end,
                
                -- Indicadores de riesgo en tiempo real
                CASE WHEN AVG(gr.grade) < 11 THEN 1 ELSE 0 END as low_performance_flag,
                CASE WHEN SAFE_DIVIDE(
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END), 
                    COUNT(a.id)
                ) * 100 < 70 THEN 1 ELSE 0 END as low_attendance_flag,
                CASE WHEN COUNT(CASE WHEN ep.status = 'pending' THEN ep.id END) > 0 THEN 1 ELSE 0 END as pending_payments_flag,
                CASE WHEN COUNT(DISTINCT CASE WHEN t.status = 'open' THEN t.id END) > 2 THEN 1 ELSE 0 END as many_open_tickets_flag,
                
                -- Estado actual para referencia
                e.academic_status,
                e.payment_status

            FROM `lms_analytics.enrollments` e
            JOIN `lms_analytics.groups` g ON e.group_id = g.id
            JOIN `lms_analytics.users` u ON e.user_id = u.id
            LEFT JOIN `lms_analytics.attendances` a ON e.id = a.enrollment_id
            LEFT JOIN `lms_analytics.grades` gr ON e.id = gr.enrollment_id
            LEFT JOIN `lms_analytics.enrollment_payments` ep ON e.id = ep.enrollment_id
            LEFT JOIN `lms_analytics.tickets` t ON e.user_id = t.user_id
            
            WHERE e.academic_status = 'active'  -- Solo estudiantes activos actualmente
            AND DATE(g.end_date) >= CURRENT_DATE()  -- Grupos que aún no terminan
            GROUP BY e.id, e.user_id, e.group_id, g.name, u.name, g.start_date, g.end_date, e.academic_status, e.payment_status
            HAVING total_sessions > 0  -- Solo estudiantes con al menos una sesión
            ORDER BY enrollment_id
        ";

        try {
            $queryJobConfig = $this->bigQuery->query($query);
            $queryResults = $this->bigQuery->runQuery($queryJobConfig);
            
            $results = [];
            foreach ($queryResults as $row) {
                $results[] = $row;
            }
            
            return $results;
        } catch (\Exception $e) {
            Log::error('Prediction Dataset Error: ' . $e->getMessage());
            throw new \Exception("Error generando dataset de predicción: " . $e->getMessage());
        }
    }

    /**
     * Exporta dataset de entrenamiento a CSV
     */
    public function exportTrainingDatasetToCsv(): string
    {
        $data = $this->generateTrainingDataset();
        
        if (empty($data)) {
            throw new \Exception("No hay datos de entrenamiento para exportar");
        }

        return $this->exportToCsv($data, 'training');
    }

    /**
     * Exporta dataset de predicción a CSV
     */
    public function exportPredictionDatasetToCsv(): string
    {
        $data = $this->generatePredictionDataset();
        
        if (empty($data)) {
            throw new \Exception("No hay datos de predicción para exportar");
        }

        return $this->exportToCsv($data, 'prediction');
    }

    /**
     * Función helper para exportar a CSV
     */
    private function exportToCsv(array $data, string $type): string
    {
        $filename = "dropout_{$type}_dataset_" . date('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);
        
        // Crear directorio si no existe
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $handle = fopen($filepath, 'w');
        
        // Escribir headers
        if (!empty($data)) {
            $headers = array_keys((array)$data[0]);
            fputcsv($handle, $headers);
            
            // Escribir datos
            foreach ($data as $row) {
                fputcsv($handle, (array)$row);
            }
        }
        
        fclose($handle);

        return $filepath;
    }

    /**
     * Obtiene estadísticas de ambos datasets
     */
    public function getDatasetStats(): array
    {
        try {
            $trainingData = $this->generateTrainingDataset();
            $predictionData = $this->generatePredictionDataset();
            
            $trainingStats = $this->calculateStats($trainingData, 'training');
            $predictionStats = $this->calculateStats($predictionData, 'prediction');

            return [
                'training' => $trainingStats,
                'prediction' => $predictionStats,
                'summary' => [
                    'total_training_records' => count($trainingData),
                    'total_prediction_records' => count($predictionData),
                    'training_dropout_rate' => $trainingStats['dropout_rate'] ?? 0,
                    'high_risk_students' => $predictionStats['high_risk_count'] ?? 0
                ]
            ];

        } catch (\Exception $e) {
            return ['error' => 'Error calculando estadísticas: ' . $e->getMessage()];
        }
    }

    private function calculateStats(array $data, string $type): array
    {
        if (empty($data)) {
            return ['error' => "No hay datos para {$type}"];
        }

        $stats = [
            'total_records' => count($data),
            'columns' => array_keys((array)$data[0]),
            'sample_records' => array_slice($data, 0, 3)
        ];

        // Estadísticas específicas para training
        if ($type === 'training') {
            $dropoutCount = 0;
            foreach ($data as $row) {
                if (isset($row['dropped_out']) && $row['dropped_out'] == 1) {
                    $dropoutCount++;
                }
            }
            $stats['dropout_count'] = $dropoutCount;
            $stats['dropout_rate'] = count($data) > 0 ? round(($dropoutCount / count($data)) * 100, 2) : 0;
        }

        // Estadísticas específicas para prediction
        if ($type === 'prediction') {
            $highRiskCount = 0;
            foreach ($data as $row) {
                $riskFactors = 0;
                if (isset($row['low_performance_flag']) && $row['low_performance_flag'] == 1) $riskFactors++;
                if (isset($row['low_attendance_flag']) && $row['low_attendance_flag'] == 1) $riskFactors++;
                if (isset($row['pending_payments_flag']) && $row['pending_payments_flag'] == 1) $riskFactors++;
                if ($riskFactors >= 2) $highRiskCount++;
            }
            $stats['high_risk_count'] = $highRiskCount;
            $stats['high_risk_percentage'] = count($data) > 0 ? round(($highRiskCount / count($data)) * 100, 2) : 0;
        }

        return $stats;
    }
}