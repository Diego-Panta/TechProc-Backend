<?php
// app/Console/Commands/GenerateDropoutDataset.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\DataAnalyst\Services\DropoutDatasetService;

class GenerateDropoutDataset extends Command
{
    protected $signature = 'lms:generate-dropout-dataset 
                            {--export-training : Exportar dataset de entrenamiento}
                            {--export-prediction : Exportar dataset de predicción}
                            {--stats-only : Solo mostrar estadísticas}';
    
    protected $description = 'Genera datasets para predicción de deserción estudiantil';

    public function handle(DropoutDatasetService $datasetService)
    {
        $this->info('🎯 Generando datasets para predicción de deserción...');

        try {
            if ($this->option('stats-only')) {
                $this->showStats($datasetService);
                return;
            }

            if ($this->option('export-training')) {
                $this->exportTrainingDataset($datasetService);
            }

            if ($this->option('export-prediction')) {
                $this->exportPredictionDataset($datasetService);
            }

            // Si no hay opciones, mostrar stats por defecto
            if (!$this->option('export-training') && !$this->option('export-prediction')) {
                $this->showStats($datasetService);
            }

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }
    }

    private function showStats(DropoutDatasetService $datasetService)
    {
        $stats = $datasetService->getDatasetStats();
        
        if (isset($stats['error'])) {
            $this->error("❌ Error: {$stats['error']}");
            return;
        }

        $this->info("\n📊 ESTADÍSTICAS DE DATASETS");
        $this->info("============================");
        
        $this->info("\n🎓 DATASET DE ENTRENAMIENTO (Histórico):");
        $this->info("   Total de registros: {$stats['training']['total_records']}");
        if (isset($stats['training']['dropout_count'])) {
            $this->info("   Estudiantes que desertaron: {$stats['training']['dropout_count']}");
            $this->info("   Tasa de deserción: {$stats['training']['dropout_rate']}%");
        }
        
        $this->info("\n🔮 DATASET DE PREDICCIÓN (Actual):");
        $this->info("   Total de estudiantes activos: {$stats['prediction']['total_records']}");
        if (isset($stats['prediction']['high_risk_count'])) {
            $this->info("   Estudiantes de alto riesgo: {$stats['prediction']['high_risk_count']}");
            $this->info("   Porcentaje de alto riesgo: {$stats['prediction']['high_risk_percentage']}%");
        }

        // Mostrar muestra de datos de entrenamiento
        if (isset($stats['training']['sample_records']) && !empty($stats['training']['sample_records'])) {
            $this->info("\n📋 MUESTRA - Dataset de Entrenamiento:");
            $this->table(
                array_keys((array)$stats['training']['sample_records'][0]),
                array_map(function($record) {
                    return array_map(function($value) {
                        return is_float($value) ? round($value, 2) : $value;
                    }, (array)$record);
                }, $stats['training']['sample_records'])
            );
        }

        // Mostrar muestra de datos de predicción
        if (isset($stats['prediction']['sample_records']) && !empty($stats['prediction']['sample_records'])) {
            $this->info("\n📋 MUESTRA - Dataset de Predicción:");
            $this->table(
                array_keys((array)$stats['prediction']['sample_records'][0]),
                array_map(function($record) {
                    return array_map(function($value) {
                        return is_float($value) ? round($value, 2) : $value;
                    }, (array)$record);
                }, $stats['prediction']['sample_records'])
            );
        }
    }

    private function exportTrainingDataset(DropoutDatasetService $datasetService)
    {
        $this->info('💾 Exportando dataset de ENTRENAMIENTO...');
        $filepath = $datasetService->exportTrainingDatasetToCsv();
        $this->info("✅ Dataset de entrenamiento exportado a: {$filepath}");
        
        $stats = $datasetService->getDatasetStats();
        if (isset($stats['training']['total_records'])) {
            $this->info("   Registros exportados: {$stats['training']['total_records']}");
        }
    }

    private function exportPredictionDataset(DropoutDatasetService $datasetService)
    {
        $this->info('🔮 Exportando dataset de PREDICCIÓN...');
        $filepath = $datasetService->exportPredictionDatasetToCsv();
        $this->info("✅ Dataset de predicción exportado a: {$filepath}");
        
        $stats = $datasetService->getDatasetStats();
        if (isset($stats['prediction']['total_records'])) {
            $this->info("   Estudiantes activos exportados: {$stats['prediction']['total_records']}");
        }
    }
}