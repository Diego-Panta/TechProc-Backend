<?php
// app/Domains/DataAnalyst/Http/Controllers/BigQuerySyncController.php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Google\Cloud\BigQuery\BigQueryClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BigQuerySyncController extends Controller
{
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

    /**
     * Sincronizar tablas con BigQuery
     */
    public function syncTables(Request $request): JsonResponse
    {
        $request->validate([
            'tables' => 'nullable|string',
            'incremental' => 'nullable|boolean',
            'truncate' => 'nullable|boolean'
        ]);

        $tables = $request->get('tables') 
            ? explode(',', $request->get('tables'))
            : [
                'attendances', 'class_sessions', 'enrollments', 'groups',
                'grades', 'exams', 'enrollment_results', 'modules', 'course_versions',
                'courses', 'users',
            ];

        $incremental = $request->boolean('incremental', false);
        $truncate = $request->boolean('truncate', false);

        $results = [];
        $bigQuery = $this->getBigQueryClient();
        $dataset = $bigQuery->dataset('lms_analytics');

        foreach ($tables as $table) {
            try {
                $result = $this->syncTable($table, $dataset, $incremental, $truncate);
                $results[$table] = $result;
            } catch (\Exception $e) {
                $results[$table] = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'records' => 0
                ];
                
                Log::error("Error sincronizando {$table} a BigQuery: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Sincronización completada',
            'results' => $results
        ]);
    }

    /**
     * Sincronizar una tabla específica
     */
    public function syncSingleTable(Request $request, string $table): JsonResponse
    {
        $request->validate([
            'incremental' => 'nullable|boolean',
            'truncate' => 'nullable|boolean'
        ]);

        $incremental = $request->boolean('incremental', false);
        $truncate = $request->boolean('truncate', false);

        try {
            $bigQuery = $this->getBigQueryClient();
            $dataset = $bigQuery->dataset('lms_analytics');
            
            $result = $this->syncTable($table, $dataset, $incremental, $truncate);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'records' => $result['records'],
                'table' => $table
            ]);

        } catch (\Exception $e) {
            Log::error("Error sincronizando {$table} a BigQuery: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'table' => $table
            ], 500);
        }
    }

    /**
     * Sincronización completa
     */
    public function syncFull(Request $request): JsonResponse
    {
        $request->validate([
            'tables' => 'nullable|string',
        ]);

        $tables = $request->get('tables') 
            ? explode(',', $request->get('tables'))
            : [
                'attendances', 'class_sessions', 'enrollments', 'groups',
                'grades', 'exams', 'enrollment_results', 'modules', 'course_versions',
                'courses', 'users',
            ];

        $results = [];
        $bigQuery = $this->getBigQueryClient();
        $dataset = $bigQuery->dataset('lms_analytics');

        foreach ($tables as $table) {
            try {
                $this->info("🔄 Sincronizando tabla: {$table}");
                
                $datos = DB::table($table)->get();
                
                if ($datos->isEmpty()) {
                    $results[$table] = [
                        'success' => true,
                        'message' => "No hay datos en {$table}",
                        'records' => 0
                    ];
                    continue;
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
                
                // VACIAR TABLA USANDO TRUNCATE (no DELETE)
                $this->truncateTableSafe($bigQueryTable, $table);
                
                // Insertar en lotes
                $insertedCount = $this->insertInBatches($bigQueryTable, $rows, $table);
                
                $results[$table] = [
                    'success' => true,
                    'message' => "{$table} sincronizada correctamente",
                    'records' => $insertedCount
                ];

                $this->info("✅ {$table} sincronizada correctamente (" . $insertedCount . " registros)");

            } catch (\Exception $e) {
                $results[$table] = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'records' => 0
                ];
                
                $this->error("❌ Error sincronizando {$table}: " . $e->getMessage());
                Log::error("Error sincronizando {$table} a BigQuery: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Sincronización completa finalizada',
            'results' => $results
        ]);
    }

    /**
     * Sincronización incremental
     */
    public function syncIncremental(Request $request): JsonResponse
    {
        $request->validate([
            'tables' => 'nullable|string',
        ]);

        $tables = $request->get('tables') 
            ? explode(',', $request->get('tables'))
            : [
                'attendances', 'class_sessions', 'enrollments', 'groups',
                'grades', 'exams', 'enrollment_results', 'modules', 'course_versions',
                'courses', 'users',
            ];

        $results = [];
        $bigQuery = $this->getBigQueryClient();
        $dataset = $bigQuery->dataset('lms_analytics');

        foreach ($tables as $table) {
            try {
                $this->info("🔄 Sincronización incremental tabla: {$table}");
                
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
                    $results[$table] = [
                        'success' => true,
                        'message' => "No hay nuevos datos en {$table}",
                        'records' => 0
                    ];
                    continue;
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
                
                // Insertar en lotes (sin truncar para incremental)
                $insertedCount = $this->insertInBatches($bigQueryTable, $rows, $table);

                // Actualizar último sync timestamp
                cache([$lastSyncKey => now()->toDateTimeString()], 60 * 24 * 7);

                $results[$table] = [
                    'success' => true,
                    'message' => "{$table} sincronizada incrementalmente",
                    'records' => $insertedCount,
                    'last_sync' => now()->toDateTimeString()
                ];

                $this->info("✅ {$table} sincronizada incrementalmente (" . $insertedCount . " nuevos registros)");

            } catch (\Exception $e) {
                $results[$table] = [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'records' => 0
                ];
                
                $this->error("❌ Error sincronizando incrementalmente {$table}: " . $e->getMessage());
                Log::error("Error sincronizando incrementalmente {$table} a BigQuery: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Sincronización incremental finalizada',
            'results' => $results
        ]);
    }

    /**
     * Vaciar tablas en BigQuery (TRUNCATE seguro)
     */
    public function truncateTables(Request $request): JsonResponse
    {
        $request->validate([
            'tables' => 'nullable|string',
        ]);

        $tables = $request->get('tables') 
            ? explode(',', $request->get('tables'))
            : [
                'attendances', 'class_sessions', 'enrollments', 'groups',
                'grades', 'exams', 'enrollment_results', 'modules', 'course_versions',
                'courses', 'users',
            ];

        $results = [];
        $bigQuery = $this->getBigQueryClient();
        $dataset = $bigQuery->dataset('lms_analytics');

        foreach ($tables as $table) {
            try {
                $this->info("🗑️ Vaciando tabla: {$table}");
                
                $bigQueryTable = $dataset->table($table);
                $this->truncateTableSafe($bigQueryTable, $table);
                
                $results[$table] = [
                    'success' => true,
                    'message' => "Tabla {$table} vaciada correctamente"
                ];

                $this->info("✅ Tabla {$table} vaciada correctamente");

            } catch (\Exception $e) {
                $results[$table] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
                
                $this->error("❌ Error vaciando tabla {$table}: " . $e->getMessage());
                Log::error("Error vaciando tabla {$table} en BigQuery: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Operación de vaciado completada',
            'results' => $results
        ]);
    }

    /**
     * Método seguro para truncar tablas (evita error del streaming buffer)
     */
    private function truncateTableSafe($table, string $tableName): void
    {
        try {
            $this->info("Vaciando tabla {$tableName} en BigQuery (método seguro)...");
            
            $bigQuery = $this->getBigQueryClient();
            
            // Método 1: Intentar con DELETE (puede fallar por streaming buffer)
            try {
                $query = "DELETE FROM `lms_analytics.{$tableName}` WHERE true";
                $queryJobConfig = $bigQuery->query($query);
                $bigQuery->runQuery($queryJobConfig);
                $this->info("✅ Tabla {$tableName} vaciada usando DELETE");
            } catch (\Exception $e) {
                // Si falla por streaming buffer, usar método alternativo
                if (str_contains($e->getMessage(), 'streaming buffer')) {
                    $this->info("Usando método alternativo para {$tableName} (streaming buffer detectado)...");
                    
                    // Método 2: Crear tabla temporal y reemplazar
                    $this->recreateTable($bigQuery, $tableName);
                } else {
                    throw $e;
                }
            }
            
            sleep(2); // Esperar para asegurar que se complete la operación
            
        } catch (\Exception $e) {
            $this->warn("No se pudo vaciar tabla {$tableName}: " . $e->getMessage());
            Log::warning("No se pudo vaciar tabla {$tableName}: " . $e->getMessage());
            
            // Si no se puede truncar, lanzar excepción para que el caller sepa
            throw new \Exception("No se pudo vaciar la tabla {$tableName}: " . $e->getMessage());
        }
    }

    /**
     * Método alternativo: recrear tabla para evitar streaming buffer
     */
    private function recreateTable(BigQueryClient $bigQuery, string $tableName): void
    {
        try {
            $dataset = $bigQuery->dataset('lms_analytics');
            $table = $dataset->table($tableName);
            
            // Obtener schema de la tabla original
            $tableInfo = $table->info();
            $schema = $tableInfo['schema'];
            
            // Crear nombre para tabla temporal
            $tempTableName = $tableName . '_temp_' . time();
            
            // Crear tabla temporal con mismo schema
            $tempTable = $dataset->createTable($tempTableName, ['schema' => $schema]);
            
            $this->info("Tabla temporal {$tempTableName} creada");
            
            // En un caso real, aquí copiaríamos los datos si fuera necesario
            // Pero para truncate, simplemente eliminamos la original y renombramos
            
            // Eliminar tabla original
            $table->delete();
            
            // Renombrar tabla temporal a nombre original
            // BigQuery no soporta RENAME directo, necesitamos copiar
            $newTable = $dataset->createTable($tableName, ['schema' => $schema]);
            
            // Eliminar tabla temporal
            $tempTable->delete();
            
            $this->info("✅ Tabla {$tableName} recreada exitosamente");
            
        } catch (\Exception $e) {
            $this->error("Error recreando tabla {$tableName}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener estado de sincronización
     */
    public function getSyncStatus(): JsonResponse
    {
        $tables = [
            'attendances', 'class_sessions', 'enrollments', 'groups',
            'grades', 'exams', 'enrollment_results', 'modules', 'course_versions',
            'courses', 'users',
        ];

        $status = [];

        foreach ($tables as $table) {
            try {
                $lastSync = cache("last_sync_{$table}");
                $recordCount = DB::table($table)->count();
                
                // Verificar si hay nuevos datos desde último sync
                $hasNewData = false;
                if ($lastSync) {
                    $query = DB::table($table);
                    if (Schema::hasColumn($table, 'updated_at')) {
                        $hasNewData = $query->where('updated_at', '>', $lastSync)->exists();
                    } elseif (Schema::hasColumn($table, 'created_at')) {
                        $hasNewData = $query->where('created_at', '>', $lastSync)->exists();
                    }
                }
                
                $status[$table] = [
                    'last_sync' => $lastSync,
                    'local_records' => $recordCount,
                    'has_new_data' => $hasNewData,
                    'needs_sync' => !$lastSync || $hasNewData
                ];
            } catch (\Exception $e) {
                $status[$table] = [
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'status' => $status
        ]);
    }

    // Métodos auxiliares para logging (simulan los del comando)
    private function info(string $message): void
    {
        Log::info($message);
    }

    private function warn(string $message): void
    {
        Log::warning($message);
    }

    private function error(string $message): void
    {
        Log::error($message);
    }

    /**
     * Obtener cliente de BigQuery
     */
    private function getBigQueryClient(): BigQueryClient
    {
        return new BigQueryClient([
            'projectId' => env('BIGQUERY_PROJECT_ID'),
            'keyFilePath' => base_path(env('GOOGLE_APPLICATION_CREDENTIALS')),
        ]);
    }

    /**
     * Verificar si hay nuevos datos
     */
    private function hasNewData(string $table, string $lastSync): bool
    {
        $query = DB::table($table);
        
        if (Schema::hasColumn($table, 'updated_at')) {
            $query->where('updated_at', '>', $lastSync);
        } elseif (Schema::hasColumn($table, 'created_at')) {
            $query->where('created_at', '>', $lastSync);
        }

        return $query->exists();
    }
    
    // Copia exactamente los mismos métodos de tu comando aquí...
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

    private function formatDateForBigQuery($value): string
    {
        try {
            if ($value instanceof \DateTime || $value instanceof \Carbon\Carbon) {
                return $value->format('Y-m-d');
            }
            
            $carbon = Carbon::parse($value);
            return $carbon->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("Error formateando fecha '{$value}': " . $e->getMessage());
            return (string)$value;
        }
    }

    private function formatDateTimeForBigQuery($value): string
    {
        try {
            if ($value instanceof \DateTime || $value instanceof \Carbon\Carbon) {
                return $value->format('Y-m-d H:i:s');
            }
            
            $carbon = Carbon::parse($value);
            return $carbon->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Log::warning("Error formateando datetime '{$value}': " . $e->getMessage());
            return (string)$value;
        }
    }

    private function truncateTable($table, string $tableName): void
    {
        try {
            Log::info("Vaciando tabla {$tableName} en BigQuery...");
            
            $bigQuery = $this->getBigQueryClient();
            
            $query = "DELETE FROM `lms_analytics.{$tableName}` WHERE true";
            $queryJobConfig = $bigQuery->query($query);
            $bigQuery->runQuery($queryJobConfig);
            
            Log::info("Tabla {$tableName} vaciada en BigQuery");
            sleep(2);
        } catch (\Exception $e) {
            Log::warning("No se pudo vaciar tabla {$tableName}: " . $e->getMessage());
        }
    }

    private function insertInBatches($table, array $rows, string $tableName): int
    {
        $batchSize = 100;
        $chunks = array_chunk($rows, $batchSize);
        $totalChunks = count($chunks);
        $totalInserted = 0;

        foreach ($chunks as $i => $chunk) {
            Log::info("Insertando lote " . ($i + 1) . "/{$totalChunks} en {$tableName}...");
            
            try {
                $insertResponse = $table->insertRows($chunk, [
                    'retries' => 3,
                ]);
                
                if ($insertResponse->isSuccessful()) {
                    $totalInserted += count($chunk);
                    Log::info("✅ Lote " . ($i + 1) . "/{$totalChunks} insertado correctamente en {$tableName}");
                } else {
                    $this->handleInsertErrors($insertResponse, $chunk, $tableName);
                    // Intentar insertar fila por fila para identificar el problema
                    $this->insertRowByRow($table, $chunk, $tableName);
                }
                
            } catch (\Exception $e) {
                Log::error("Error insertando lote " . ($i + 1) . " en {$tableName}: " . $e->getMessage());
            }
            
            if ($i < $totalChunks - 1) {
                usleep(500000);
            }
        }
        
        Log::info("Total insertado en {$tableName}: {$totalInserted} registros");
        return $totalInserted;
    }

    private function insertRowByRow($table, array $chunk, string $tableName): void
    {
        Log::info("Intentando inserción fila por fila para debugging en {$tableName}...");
        
        foreach ($chunk as $index => $row) {
            try {
                $insertResponse = $table->insertRows([$row]);
                
                if (!$insertResponse->isSuccessful()) {
                    Log::error("Fila {$index} falló en {$tableName}:");
                    $this->handleInsertErrors($insertResponse, [$row], $tableName);
                } else {
                    Log::info("✅ Fila {$index} insertada correctamente en {$tableName}");
                }
            } catch (\Exception $e) {
                Log::error("Error en fila {$index} de {$tableName}: " . $e->getMessage());
                Log::error("Datos de la fila: " . json_encode($row));
            }
        }
    }

    private function handleInsertErrors($insertResponse, array $chunk, string $tableName): void
    {
        $failedRows = $insertResponse->failedRows();
        
        if (!empty($failedRows)) {
            Log::error("Errores en inserción de {$tableName}:");
            
            foreach ($failedRows as $row) {
                Log::error("Fila fallida en {$tableName}: " . json_encode($row['rowData'] ?? []));
                Log::error("Errores: " . json_encode($row['errors'] ?? []));
            }
        }
    }
}