<?php
// app/Console/Commands/SyncLmsToBigQuery.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Google\Cloud\BigQuery\BigQueryClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncLmsToBigQuery extends Command
{
    protected $signature = 'lms:sync-bigquery 
                            {--incremental : Sincronización incremental basada en updated_at}
                            {--tables= : Tablas específicas a sincronizar (separadas por coma)}
                            {--truncate : Vaciar tablas en BigQuery antes de sincronizar}';
    
    protected $description = 'Sincroniza tablas del LMS hacia BigQuery de forma optimizada.';

    // Mapeo de tipos de campos para conversión correcta
    protected $fieldTypes = [
        'groups' => [
            'start_date' => 'date',
            'end_date' => 'date'
        ],
        'class_sessions' => [
            'start_time' => 'datetime', 
            'end_time' => 'datetime'
        ],
        'exams' => [
            'start_time' => 'datetime',
            'end_time' => 'datetime'
        ],
        'enrollment_payments' => [
            'operation_date' => 'datetime'
        ],
        'appointments' => [
            'start_time' => 'datetime',
            'end_time' => 'datetime'
        ],
        'survey_responses' => [
            'date' => 'datetime'
        ]
    ];

    public function handle()
    {
        $bigQuery = new BigQueryClient([
            'projectId' => env('BIGQUERY_PROJECT_ID'),
            'keyFilePath' => base_path(env('GOOGLE_APPLICATION_CREDENTIALS')),
        ]);

        $dataset = $bigQuery->dataset('lms_analytics');

        $tables = $this->option('tables') 
            ? explode(',', $this->option('tables'))
            : [
                'attendances', 'class_sessions', 'enrollments', 'groups',
                'grades', 'exams', 'enrollment_results', 'modules', 'course_versions',
                'courses', 'users', 'group_teachers', 'teacher_profiles'
            ];

        foreach ($tables as $table) {
            $this->info("🔄 Sincronizando tabla: {$table}");
            
            try {
                if ($this->option('incremental')) {
                    $this->syncIncremental($table, $dataset);
                } else {
                    $this->syncFull($table, $dataset);
                }

            } catch (\Exception $e) {
                $this->error("❌ Error sincronizando {$table}: " . $e->getMessage());
                Log::error("Error sincronizando {$table} a BigQuery: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info('🎉 Sincronización completa con BigQuery.');
    }

    /**
     * Sincronización completa
     */
    private function syncFull(string $table, $dataset): void
    {
        $this->info("Obteniendo datos de {$table}...");
        
        $datos = DB::table($table)->get();
        
        if ($datos->isEmpty()) {
            $this->warn("No hay datos en {$table}");
            return;
        }

        $this->info("Procesando " . $datos->count() . " registros...");

        // Convertir a formato correcto para BigQuery
        $rows = $datos->map(function ($row) use ($table) {
            return [
                'insertId' => uniqid(),
                'data' => $this->formatRowForBigQuery((array)$row, $table)
            ];
        })->toArray();

        $bigQueryTable = $dataset->table($table);
        
        // Vaciar tabla si se solicita
        if ($this->option('truncate')) {
            $this->truncateTable($bigQueryTable, $table);
        }
        
        // Insertar en lotes
        $this->insertInBatches($bigQueryTable, $rows, $table);
        
        $this->info("✅ {$table} sincronizada correctamente (" . count($rows) . " registros)");
    }

    /**
     * Sincronización incremental
     */
    private function syncIncremental(string $table, $dataset): void
    {
        // Obtener último timestamp sincronizado
        $lastSyncKey = "last_sync_{$table}";
        $lastSync = cache($lastSyncKey) ?: '1970-01-01 00:00:00';

        $query = DB::table($table);
        
        // Si la tabla tiene updated_at, usar para incremental
        if (Schema::hasColumn($table, 'updated_at')) {
            $query->where('updated_at', '>', $lastSync);
        } elseif (Schema::hasColumn($table, 'created_at')) {
            $query->where('created_at', '>', $lastSync);
        }

        $newData = $query->get();

        if ($newData->isEmpty()) {
            $this->info("No hay nuevos datos en {$table}");
            return;
        }

        $this->info("Procesando " . $newData->count() . " nuevos registros...");

        // Convertir a formato correcto para BigQuery
        $rows = $newData->map(function ($row) use ($table) {
            return [
                'insertId' => uniqid(),
                'data' => $this->formatRowForBigQuery((array)$row, $table)
            ];
        })->toArray();

        $bigQueryTable = $dataset->table($table);
        
        // Insertar en lotes
        $this->insertInBatches($bigQueryTable, $rows, $table);

        // Actualizar último sync timestamp
        cache([$lastSyncKey => now()->toDateTimeString()], 60 * 24 * 7);
        
        $this->info("✅ {$table} sincronizada incrementalmente (" . count($rows) . " nuevos registros)");
    }

    /**
     * Formatea una fila para BigQuery con manejo correcto de tipos
     */
    private function formatRowForBigQuery(array $row, string $tableName): array
    {
        $formatted = [];
        
        foreach ($row as $key => $value) {
            if ($value === null) {
                $formatted[$key] = null;
                continue;
            }

            // Manejar tipos específicos por tabla y campo
            $fieldType = $this->getFieldType($tableName, $key);
            
            switch ($fieldType) {
                case 'date':
                    $formatted[$key] = $this->formatDateForBigQuery($value);
                    break;
                    
                case 'datetime':
                    $formatted[$key] = $this->formatDateTimeForBigQuery($value);
                    break;
                    
                case 'integer':
                    $formatted[$key] = (int)$value;
                    break;
                    
                case 'float':
                    $formatted[$key] = (float)$value;
                    break;
                    
                default:
                    // Conversión automática basada en nombre de campo
                    $formatted[$key] = $this->autoConvertField($key, $value);
                    break;
            }
        }

        return $formatted;
    }

    /**
     * Obtiene el tipo de campo específico
     */
    private function getFieldType(string $tableName, string $fieldName): ?string
    {
        // Primero verificar mapeo específico por tabla
        if (isset($this->fieldTypes[$tableName][$fieldName])) {
            return $this->fieldTypes[$tableName][$fieldName];
        }

        // Lógica general basada en nombres de campo
        if (str_ends_with($fieldName, '_date') || $fieldName === 'start_date' || $fieldName === 'end_date') {
            return 'date';
        }
        
        if (str_ends_with($fieldName, '_time') || $fieldName === 'created_at' || $fieldName === 'updated_at') {
            return 'datetime';
        }
        
        if (str_ends_with($fieldName, '_id') || $fieldName === 'id') {
            return 'integer';
        }
        
        if (in_array($fieldName, ['grade', 'final_grade', 'attendance_percentage', 'amount'])) {
            return 'float';
        }

        return null;
    }

    /**
     * Conversión automática basada en nombre de campo
     */
    private function autoConvertField(string $key, $value)
    {
        if (is_numeric($value)) {
            if (str_contains($key, '_id') || $key === 'id') {
                return (int)$value;
            } elseif (in_array($key, ['grade', 'final_grade', 'attendance_percentage', 'amount'])) {
                return (float)$value;
            } elseif (in_array($key, ['sort', 'score'])) {
                return (int)$value;
            }
        }
        
        // Para strings, mantener como están
        return (string)$value;
    }

    /**
     * Formatea fecha para BigQuery (YYYY-MM-DD)
     */
    private function formatDateForBigQuery($value): string
    {
        try {
            if ($value instanceof \DateTime || $value instanceof \Carbon\Carbon) {
                return $value->format('Y-m-d');
            }
            
            $carbon = Carbon::parse($value);
            return $carbon->format('Y-m-d');
        } catch (\Exception $e) {
            $this->warn("Error formateando fecha '{$value}': " . $e->getMessage());
            return (string)$value;
        }
    }

    /**
     * Formatea fecha/hora para BigQuery (YYYY-MM-DD HH:MM:SS)
     */
    private function formatDateTimeForBigQuery($value): string
    {
        try {
            if ($value instanceof \DateTime || $value instanceof \Carbon\Carbon) {
                return $value->format('Y-m-d H:i:s');
            }
            
            $carbon = Carbon::parse($value);
            return $carbon->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            $this->warn("Error formateando datetime '{$value}': " . $e->getMessage());
            return (string)$value;
        }
    }

    /**
     * Vaciar tabla en BigQuery
     */
    private function truncateTable($table, string $tableName): void
    {
        try {
            $this->info("Vaciando tabla {$tableName} en BigQuery...");
            
            $bigQuery = new BigQueryClient([
                'projectId' => env('BIGQUERY_PROJECT_ID'),
                'keyFilePath' => base_path(env('GOOGLE_APPLICATION_CREDENTIALS')),
            ]);
            
            $query = "DELETE FROM `lms_analytics.{$tableName}` WHERE true";
            $queryJobConfig = $bigQuery->query($query);
            $bigQuery->runQuery($queryJobConfig);
            
            $this->info("Tabla {$tableName} vaciada en BigQuery");
            sleep(2);
        } catch (\Exception $e) {
            $this->warn("No se pudo vaciar tabla {$tableName}: " . $e->getMessage());
        }
    }

    /**
     * Insertar datos en lotes
     */
    private function insertInBatches($table, array $rows, string $tableName): void
    {
        $batchSize = 100;
        $chunks = array_chunk($rows, $batchSize);
        $totalChunks = count($chunks);
        $totalInserted = 0;

        foreach ($chunks as $i => $chunk) {
            $this->info("Insertando lote " . ($i + 1) . "/{$totalChunks}...");
            
            try {
                $insertResponse = $table->insertRows($chunk, [
                    'retries' => 3,
                ]);
                
                if ($insertResponse->isSuccessful()) {
                    $totalInserted += count($chunk);
                    $this->info("✅ Lote " . ($i + 1) . "/{$totalChunks} insertado correctamente");
                } else {
                    $this->handleInsertErrors($insertResponse, $chunk, $tableName);
                    // Intentar insertar fila por fila para identificar el problema
                    $this->insertRowByRow($table, $chunk, $tableName);
                }
                
            } catch (\Exception $e) {
                $this->error("Error insertando lote " . ($i + 1) . ": " . $e->getMessage());
                Log::error("Error insertando lote en {$tableName}: " . $e->getMessage());
            }
            
            if ($i < $totalChunks - 1) {
                usleep(500000);
            }
        }
        
        $this->info("Total insertado en {$tableName}: {$totalInserted} registros");
    }

    /**
     * Inserta fila por fila para debugging
     */
    private function insertRowByRow($table, array $chunk, string $tableName): void
    {
        $this->info("Intentando inserción fila por fila para debugging...");
        
        foreach ($chunk as $index => $row) {
            try {
                $insertResponse = $table->insertRows([$row]);
                
                if (!$insertResponse->isSuccessful()) {
                    $this->error("Fila {$index} falló:");
                    $this->handleInsertErrors($insertResponse, [$row], $tableName);
                } else {
                    $this->info("✅ Fila {$index} insertada correctamente");
                }
            } catch (\Exception $e) {
                $this->error("Error en fila {$index}: " . $e->getMessage());
                $this->error("Datos de la fila: " . json_encode($row));
            }
        }
    }

    /**
     * Maneja errores de inserción
     */
    private function handleInsertErrors($insertResponse, array $chunk, string $tableName): void
    {
        $failedRows = $insertResponse->failedRows();
        
        if (!empty($failedRows)) {
            $this->error("Errores en inserción de {$tableName}:");
            
            foreach ($failedRows as $row) {
                $this->error("Fila fallida: " . json_encode($row['rowData'] ?? []));
                $this->error("Errores: " . json_encode($row['errors'] ?? []));
                
                Log::error("Error insertando fila en {$tableName}", [
                    'rowData' => $row['rowData'] ?? [],
                    'errors' => $row['errors'] ?? []
                ]);
            }
        }
    }
}