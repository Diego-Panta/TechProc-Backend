<?php
// app/Console/Commands/SyncPredictionDataset.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\DataAnalyst\Services\DropoutDatasetSyncService;

class SyncPredictionDataset extends Command
{
    protected $signature = 'lms:sync-prediction-dataset 
                            {--incremental : Sincronización incremental}
                            {--force : Forzar sincronización completa}';
    
    protected $description = 'Sincroniza el dataset de predicción con BigQuery';

    public function handle(DropoutDatasetSyncService $syncService)
    {
        $this->info('🔄 Iniciando sincronización del dataset de predicción...');

        try {
            // Verificar estado actual
            $status = $syncService->getSyncStatus();
            $this->info("📊 Estado actual:");
            $this->info("   - Tabla existe: " . ($status['table_exists'] ? '✅' : '❌'));
            $this->info("   - Registros en BigQuery: " . ($status['record_count'] ?? 0));
            $this->info("   - Registros locales: " . ($status['local_count'] ?? 0));

            if (isset($status['error'])) {
                $this->warn("   - Error: " . $status['error']);
            }

            if ($this->option('incremental')) {
                $this->info('🔄 Realizando sincronización incremental...');
                $result = null;
            } else {
                $this->info('🔄 Realizando sincronización completa...');
                $result = $syncService->syncPredictionDataset();
            }

            if ($result['success']) {
                $this->info("✅ " . $result['message']);
                $this->info("📈 Registros sincronizados: " . $result['records_synced']);
                
                // Mostrar estado final
                $finalStatus = $syncService->getSyncStatus();
                $this->info("📊 Estado final:");
                $this->info("   - Registros en BigQuery: " . ($finalStatus['record_count'] ?? 0));
                $this->info("   - Estado: " . ($finalStatus['sync_status'] ?? 'UNKNOWN'));
                
            } else {
                $this->error("❌ Error: " . $result['error']);
                if (isset($result['debug_info'])) {
                    $this->error("🔍 Debug: " . $result['debug_info']);
                }
            }

        } catch (\Exception $e) {
            $this->error("❌ Error fatal: " . $e->getMessage());
            $this->error("📝 Trace: " . $e->getTraceAsString());
        }
    }
}