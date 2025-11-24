<?php

namespace App\Domains\DataAnalyst\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DropoutDatasetSyncService
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
     * Método más simple y efectivo: CREATE OR REPLACE TABLE
     */
    public function syncPredictionDataset(): array
    {
        try {
            Log::info('🎯 Iniciando sincronización con CREATE OR REPLACE TABLE...');

            // 1. Generar dataset actualizado
            $dataset = $this->generateCurrentPredictionDataset();
            
            if (empty($dataset)) {
                Log::info('No hay datos para sincronizar');
                return [
                    'success' => true,
                    'records_synced' => 0,
                    'message' => 'No hay datos para sincronizar'
                ];
            }

            Log::info('Dataset generado con ' . count($dataset) . ' registros');

            // 2. Construir query de CREATE OR REPLACE TABLE
            $query = $this->buildCreateReplaceQuery($dataset);
            
            // 3. Ejecutar la consulta
            Log::info('Ejecutando CREATE OR REPLACE TABLE...');
            $queryJobConfig = $this->bigQuery->query($query);
            $job = $this->bigQuery->runQuery($queryJobConfig);
            
            // Esperar a que termine
            $job->reload();
            while (!$job->isComplete()) {
                sleep(2);
                $job->reload();
            }

            Log::info('✅ Sincronización completada: ' . count($dataset) . ' registros');

            return [
                'success' => true,
                'records_synced' => count($dataset),
                'message' => 'Dataset sincronizado correctamente usando CREATE OR REPLACE TABLE'
            ];

        } catch (\Exception $e) {
            Log::error('❌ Error sincronizando dataset: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'records_synced' => 0
            ];
        }
    }

    /**
     * Construye la consulta CREATE OR REPLACE TABLE
     */
    private function buildCreateReplaceQuery(array $dataset): string
    {
        $selectQueries = [];

        foreach ($dataset as $record) {
            $selectQuery = "SELECT " . implode(', ', [
                $record['enrollment_id'] . " as enrollment_id",
                ($record['user_id'] ?? 0) . " as user_id",
                ($record['group_id'] ?? 0) . " as group_id",
                ($record['course_version_id'] ?? 0) . " as course_version_id",
                
                // FLOAT64 fields
                ($record['avg_grade'] ?? 0.0) . " as avg_grade",
                ($record['grade_std_dev'] ?? 0.0) . " as grade_std_dev",
                ($record['grade_trend'] ?? 0.0) . " as grade_trend",
                ($record['max_grade'] ?? 0.0) . " as max_grade",
                ($record['min_grade'] ?? 0.0) . " as min_grade",
                ($record['grade_range'] ?? 0.0) . " as grade_range",
                ($record['attendance_trend'] ?? 0.0) . " as attendance_trend",
                ($record['exam_participation_rate'] ?? 0.0) . " as exam_participation_rate",
                ($record['payment_regularity'] ?? 0.0) . " as payment_regularity",
                ($record['course_progress'] ?? 0.0) . " as course_progress",
                ($record['sessions_progress'] ?? 0.0) . " as sessions_progress",
                ($record['historical_avg_grade'] ?? 10.0) . " as historical_avg_grade",
                ($record['avg_satisfaction_score'] ?? 3.0) . " as avg_satisfaction_score",
                
                // INT64 fields
                ($record['total_exams_taken'] ?? 0) . " as total_exams_taken",
                ($record['total_sessions'] ?? 0) . " as total_sessions",
                ($record['present_count'] ?? 0) . " as present_count",
                ($record['recent_sessions_14d'] ?? 0) . " as recent_sessions_14d",
                ($record['days_since_last_payment'] ?? 90) . " as days_since_last_payment",
                ($record['avg_payment_delay'] ?? 0) . " as avg_payment_delay",
                ($record['total_payments'] ?? 0) . " as total_payments",
                ($record['days_since_start'] ?? 0) . " as days_since_start",
                ($record['days_until_end'] ?? 0) . " as days_until_end",
                ($record['previous_courses_completed'] ?? 0) . " as previous_courses_completed",
                ($record['attendance_rate'] ?? 0) . " as attendance_rate",
                
                // Strings y fechas
                "'" . $this->escapeString($record['student_name'] ?? '') . "' as student_name",
                "'" . $this->escapeString($record['group_name'] ?? '') . "' as group_name",
                "'" . ($record['start_date'] ?? '2025-01-01') . "' as start_date",
                "'" . ($record['end_date'] ?? '2025-12-31') . "' as end_date"
            ]);

            $selectQueries[] = $selectQuery;
        }

        // Si no hay datos, crear tabla vacía
        if (empty($selectQueries)) {
            return $this->getEmptyTableQuery();
        }

        // Unir todas las consultas SELECT con UNION ALL
        $unionQuery = implode(' UNION ALL ', $selectQueries);

        return "
            CREATE OR REPLACE TABLE `lms_analytics.dataset_prediccion` AS
            {$unionQuery}
        ";
    }

    /**
     * Query para crear tabla vacía
     */
    private function getEmptyTableQuery(): string
    {
        return "
            CREATE OR REPLACE TABLE `lms_analytics.dataset_prediccion` (
                enrollment_id INT64 NOT NULL,
                user_id INT64,
                group_id INT64,
                course_version_id INT64,
                
                -- FLOAT64 fields
                avg_grade FLOAT64,
                grade_std_dev FLOAT64,
                grade_trend FLOAT64,
                max_grade FLOAT64,
                min_grade FLOAT64,
                grade_range FLOAT64,
                attendance_trend FLOAT64,
                exam_participation_rate FLOAT64,
                payment_regularity FLOAT64,
                course_progress FLOAT64,
                sessions_progress FLOAT64,
                historical_avg_grade FLOAT64,
                avg_satisfaction_score FLOAT64,
                
                -- INT64 fields
                total_exams_taken INT64,
                total_sessions INT64,
                present_count INT64,
                recent_sessions_14d INT64,
                days_since_last_payment INT64,
                avg_payment_delay INT64,
                total_payments INT64,
                days_since_start INT64,
                days_until_end INT64,
                previous_courses_completed INT64,
                attendance_rate INT64,
                
                -- Strings y fechas
                student_name STRING,
                group_name STRING,
                start_date DATE,
                end_date DATE
            )
        ";
    }

    /**
     * Escapa strings para SQL
     */
    private function escapeString(string $value): string
    {
        return str_replace("'", "''", $value);
    }

    /**
     * Genera el dataset de predicción actualizado desde la base de datos local
     * CONSULTAS CORREGIDAS para evitar duplicación de registros
     */
    public function generateCurrentPredictionDataset(): array
    {
        Log::info('📊 Generando dataset de predicción actualizado...');

        // Primero obtenemos los datos académicos sin JOINs que dupliquen
        $academicData = DB::table('enrollments as e')
            ->select([
                'e.id as enrollment_id',
                'e.user_id',
                'e.group_id',
                'g.course_version_id',
                
                // Datos académicos - usando subconsultas para evitar duplicación
                DB::raw("COALESCE((
                    SELECT ROUND(AVG(gr.grade), 2) 
                    FROM grades gr 
                    WHERE gr.enrollment_id = e.id
                ), 0) as avg_grade"),
                
                DB::raw("COALESCE((
                    SELECT ROUND(STDDEV(gr.grade), 2) 
                    FROM grades gr 
                    WHERE gr.enrollment_id = e.id
                ), 0) as grade_std_dev"),
                
                DB::raw("COALESCE((
                    SELECT COUNT(gr.id) 
                    FROM grades gr 
                    WHERE gr.enrollment_id = e.id
                ), 0) as total_exams_taken"),
                
                DB::raw("COALESCE((
                    SELECT ROUND((MAX(gr.grade) - MIN(gr.grade)) / GREATEST(COUNT(gr.id), 1), 3)
                    FROM grades gr 
                    WHERE gr.enrollment_id = e.id
                ), 0) as grade_trend"),
                
                DB::raw("COALESCE((
                    SELECT ROUND(MAX(gr.grade), 2) 
                    FROM grades gr 
                    WHERE gr.enrollment_id = e.id
                ), 0) as max_grade"),
                
                DB::raw("COALESCE((
                    SELECT ROUND(MIN(gr.grade), 2) 
                    FROM grades gr 
                    WHERE gr.enrollment_id = e.id
                ), 0) as min_grade"),
                
                DB::raw("COALESCE((
                    SELECT ROUND(MAX(gr.grade) - MIN(gr.grade), 2) 
                    FROM grades gr 
                    WHERE gr.enrollment_id = e.id
                ), 0) as grade_range"),
                
                // Datos de asistencia - usando subconsultas
                DB::raw("COALESCE((
                    SELECT ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / GREATEST(COUNT(a.id), 1)) * 100, 2)
                    FROM attendances a 
                    WHERE a.enrollment_id = e.id
                ), 0) as attendance_rate"),
                
                DB::raw("COALESCE((
                    SELECT COUNT(a.id) 
                    FROM attendances a 
                    WHERE a.enrollment_id = e.id
                ), 0) as total_sessions"),
                
                DB::raw("COALESCE((
                    SELECT SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) 
                    FROM attendances a 
                    WHERE a.enrollment_id = e.id
                ), 0) as present_count"),
                
                DB::raw("COALESCE((
                    SELECT COUNT(a.id)
                    FROM attendances a 
                    WHERE a.enrollment_id = e.id 
                    AND a.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 14 DAY)
                ), 0) as recent_sessions_14d"),
                
                // Tasa de participación en exámenes
                DB::raw("COALESCE((
                    SELECT ROUND(
                        COUNT(gr.id) / GREATEST((
                            SELECT COUNT(ex.id) 
                            FROM exams ex 
                            WHERE ex.group_id = g.id
                        ), 1), 
                    2)
                    FROM grades gr 
                    WHERE gr.enrollment_id = e.id
                ), 0) as exam_participation_rate"),
                
                // Datos de pagos
                DB::raw("COALESCE((
                    SELECT ROUND(SUM(CASE WHEN ep.status = 'approved' THEN 1 ELSE 0 END) / GREATEST(COUNT(ep.id), 1), 2)
                    FROM enrollment_payments ep 
                    WHERE ep.enrollment_id = e.id
                ), 0) as payment_regularity"),
                
                DB::raw("COALESCE((
                    SELECT DATEDIFF(CURRENT_DATE, MAX(ep.operation_date))
                    FROM enrollment_payments ep 
                    WHERE ep.enrollment_id = e.id AND ep.status = 'approved'
                ), 90) as days_since_last_payment"),
                
                DB::raw("COALESCE((
                    SELECT ROUND(AVG(DATEDIFF(ep.operation_date, g.start_date)), 1)
                    FROM enrollment_payments ep 
                    WHERE ep.enrollment_id = e.id AND ep.status = 'approved'
                ), 0) as avg_payment_delay"),
                
                DB::raw("COALESCE((
                    SELECT COUNT(ep.id)
                    FROM enrollment_payments ep 
                    WHERE ep.enrollment_id = e.id
                ), 0) as total_payments"),
                
                // Datos temporales
                DB::raw("DATEDIFF(CURRENT_DATE, g.start_date) as days_since_start"),
                DB::raw("DATEDIFF(g.end_date, CURRENT_DATE) as days_until_end"),
                DB::raw("ROUND(DATEDIFF(CURRENT_DATE, g.start_date) / GREATEST(DATEDIFF(g.end_date, g.start_date), 1), 2) as course_progress"),
                
                // Progreso de sesiones
                DB::raw("COALESCE((
                    SELECT ROUND(
                        COUNT(DISTINCT CASE WHEN cs.start_time <= NOW() THEN cs.id END) / 
                        GREATEST(COUNT(DISTINCT cs.id), 1), 
                    2)
                    FROM class_sessions cs 
                    WHERE cs.group_id = g.id
                ), 0) as sessions_progress"),
                
                // Historial previo
                DB::raw("COALESCE((
                    SELECT COUNT(DISTINCT e2.id) 
                    FROM enrollments e2 
                    WHERE e2.user_id = e.user_id AND e2.academic_status = 'completed'
                ), 0) as previous_courses_completed"),
                
                DB::raw("COALESCE((
                    SELECT ROUND(AVG(er.final_grade), 2) 
                    FROM enrollment_results er 
                    JOIN enrollments e2 ON er.enrollment_id = e2.id 
                    WHERE e2.user_id = e.user_id
                ), 10) as historical_avg_grade"),
                
                // Satisfacción
                DB::raw("COALESCE((
                    SELECT ROUND(AVG(rd.score), 2) 
                    FROM response_details rd 
                    JOIN survey_responses sr ON rd.survey_response_id = sr.id 
                    WHERE sr.user_id = e.user_id
                ), 3) as avg_satisfaction_score"),
                
                // Tendencias - cálculo simplificado
                DB::raw("0.0 as attendance_trend"), // Por simplicidad por ahora
                
                // Metadatos
                'u.name as student_name',
                'g.name as group_name',
                'g.start_date',
                'g.end_date'
            ])
            ->join('groups as g', 'e.group_id', '=', 'g.id')
            ->join('users as u', 'e.user_id', '=', 'u.id')
            ->where('e.academic_status', 'active')
            ->where('g.end_date', '>=', now())
            ->groupBy('e.id', 'e.user_id', 'e.group_id', 'g.course_version_id', 'u.name', 'g.name', 'g.start_date', 'g.end_date')
            ->get()
            ->map(function ($item) {
                return $this->ensureDataTypes((array)$item);
            })
            ->toArray();

        Log::info('Dataset generado con ' . count($academicData) . ' registros');

        return $academicData;
    }

    /**
     * Asegura tipos de datos consistentes con el modelo ML
     */
    private function ensureDataTypes(array $record): array
    {
        // Campos que deben ser INT64 según el modelo
        $intFields = [
            'total_exams_taken', 'total_sessions', 'present_count', 
            'recent_sessions_14d', 'days_since_last_payment', 
            'avg_payment_delay', 'total_payments', 'days_since_start',
            'days_until_end', 'previous_courses_completed', 'attendance_rate'
        ];

        // Campos que deben ser FLOAT64 según el modelo
        $floatFields = [
            'avg_grade', 'grade_std_dev', 'grade_trend', 'max_grade', 
            'min_grade', 'grade_range', 'attendance_trend', 
            'exam_participation_rate', 'payment_regularity', 
            'course_progress', 'sessions_progress', 'historical_avg_grade',
            'avg_satisfaction_score'
        ];

        foreach ($intFields as $field) {
            if (!isset($record[$field]) || $record[$field] === null) {
                $record[$field] = 0;
            } else {
                // Convertir a entero, redondeando si es necesario
                $record[$field] = (int) round((float) $record[$field]);
            }
        }

        foreach ($floatFields as $field) {
            if (!isset($record[$field]) || $record[$field] === null) {
                $record[$field] = 0.0;
            } else {
                $record[$field] = (float) $record[$field];
            }
        }

        // Asegurar que las fechas estén en formato correcto
        if (isset($record['start_date']) && $record['start_date'] instanceof \DateTime) {
            $record['start_date'] = $record['start_date']->format('Y-m-d');
        }

        if (isset($record['end_date']) && $record['end_date'] instanceof \DateTime) {
            $record['end_date'] = $record['end_date']->format('Y-m-d');
        }

        return $record;
    }

    /**
     * Verifica el estado de la sincronización
     */
    public function getSyncStatus(): array
    {
        try {
            $table = $this->dataset->table('dataset_prediccion');
            $tableExists = $table->exists();

            if (!$tableExists) {
                return [
                    'table_exists' => false,
                    'record_count' => 0,
                    'last_sync' => null
                ];
            }

            // Contar registros en BigQuery
            $query = "SELECT COUNT(*) as record_count FROM `lms_analytics.dataset_prediccion`";
            $queryJobConfig = $this->bigQuery->query($query);
            $results = iterator_to_array($this->bigQuery->runQuery($queryJobConfig));

            $recordCount = $results[0]['record_count'] ?? 0;

            // Contar registros locales activos
            $localCount = DB::table('enrollments as e')
                ->join('groups as g', 'e.group_id', '=', 'g.id')
                ->where('e.academic_status', 'active')
                ->where('g.end_date', '>=', now())
                ->count();

            return [
                'table_exists' => true,
                'record_count' => $recordCount,
                'local_count' => $localCount,
                'sync_status' => $recordCount == $localCount ? 'SYNCED' : 'OUT_OF_SYNC',
                'last_checked' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'table_exists' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}