<?php
// app/Console/Commands/GenerateLocalDropoutDataset.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\DataAnalyst\Services\LocalDropoutDatasetService;

class GenerateLocalDropoutDataset extends Command
{
    protected $signature = 'lms:generate-local-dropout-dataset 
                            {--historical : Exportar dataset histórico PARA ENTRENAMIENTO}
                            {--current : Exportar dataset actual PARA PREDICCIÓN}
                            {--historical-pred : Exportar dataset histórico SIN dropped_out para pruebas}
                            {--records=300 : Número de registros históricos}
                            {--stats : Mostrar estadísticas}
                            {--all : Exportar todos los datasets}';

    protected $description = 'Genera datasets locales con tipos de datos consistentes';

    public function handle(LocalDropoutDatasetService $datasetService)
    {
        $this->info('🎯 Generando datasets con TIPOS DE DATOS CONSISTENTES...');

        try {
            if ($this->option('stats')) {
                $this->showStats($datasetService);
                return;
            }

            $exportedFiles = [];
            $numRecords = (int)$this->option('records');

            if ($this->option('historical') || $this->option('all')) {
                $this->info("Generando {$numRecords} registros históricos PARA ENTRENAMIENTO...");
                $file = $datasetService->exportExtendedHistoricalDatasetToCsv($numRecords);
                $exportedFiles[] = ['type' => 'ENTRENAMIENTO', 'file' => $file];
                $this->info("Con columna 'dropped_out' para entrenamiento del modelo");
            }

            if ($this->option('current') || $this->option('all')) {
                $this->info("🔮 Generando dataset actual PARA PREDICCIÓN...");
                $file = $datasetService->exportCurrentPredictionDatasetToCsv();
                $exportedFiles[] = ['type' => 'PREDICCIÓN ACTUAL', 'file' => $file];
                $this->info("Datos reales de estudiantes activos");
            }

            if ($this->option('historical-pred')) {
                $this->info("Generando dataset histórico PARA PRUEBAS DE PREDICCIÓN...");
                $file = $datasetService->exportHistoricalForPredictionToCsv(100);
                $exportedFiles[] = ['type' => 'PRUEBAS PREDICCIÓN', 'file' => $file];
                $this->info("Sin columna 'dropped_out' - para probar el modelo entrenado");
            }

            if (empty($exportedFiles)) {
                $this->showStats($datasetService);
            } else {
                $this->info("\nARCHIVOS EXPORTADOS:");
                $this->table(
                    ['TIPO', 'ARCHIVO', 'COLUMNAS DESTACADAS'],
                    array_map(function ($item) {
                        $filename = basename($item['file']);
                        $features = $item['type'] === 'ENTRENAMIENTO' ? 'Incluye dropped_out' : 'Listo para predicción';
                        return [$item['type'], $filename, $features];
                    }, $exportedFiles)
                );

                $this->info("\nFLUJO RECOMENDADO:");
                $this->info("   1. Usar 'ENTRENAMIENTO' para crear el modelo en BigQuery");
                $this->info("   2. Una vez entrenado, usar 'PREDICCIÓN ACTUAL' para predecir riesgos");
                $this->info("   3. Opcional: Usar 'PRUEBAS PREDICCIÓN' para validar el modelo");

                $this->info("\nNOTA: Todos los datasets tienen las MISMAS columnas y tipos de datos");
                $this->info("   • grade_range es FLOAT en todos los datasets");
                $this->info("   • Los flags son INT (0/1) en todos los datasets");
                $this->info("   • Las tasas son FLOAT en todos los datasets");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }
    }

    private function showStats(LocalDropoutDatasetService $datasetService)
    {
        $this->info("🔍 Generando análisis de distribución...");

        // Análisis de distribución de dropped_out
        $dropoutAnalysis = $datasetService->analyzeDropoutDistribution();

        $this->info("\n📊 DISTRIBUCIÓN DE ABANDONOS:");
        $this->info("=============================");
        $this->info("Total de registros: {$dropoutAnalysis['total_records']}");
        $this->info("Abandonos: {$dropoutAnalysis['dropped_out_count']}");
        $this->info("Porcentaje: {$dropoutAnalysis['dropped_out_percentage']}%");

        if (!empty($dropoutAnalysis['by_profile'])) {
            $this->info("\n📈 Por perfil estudiantil:");
            foreach ($dropoutAnalysis['by_profile'] as $profile => $count) {
                $percentage = round(($count / $dropoutAnalysis['dropped_out_count']) * 100, 1);
                $this->info("   {$profile}: {$count} ({$percentage}%)");
            }
        }

        if (!empty($dropoutAnalysis['by_risk_level'])) {
            $this->info("\n🚨 Por nivel de riesgo:");
            foreach ($dropoutAnalysis['by_risk_level'] as $risk => $count) {
                $percentage = round(($count / $dropoutAnalysis['dropped_out_count']) * 100, 1);
                $this->info("   {$risk}: {$count} ({$percentage}%)");
            }
        }
        $this->info("🔍 Generando datasets de muestra para comparación...");

        $historicalSample = $datasetService->generateExtendedHistoricalDataset(5);
        $currentSample = $datasetService->generateCurrentPredictionDataset();

        $this->info("\n📊 COMPARACIÓN DE COLUMNAS Y TIPOS:");
        $this->info("=====================================");

        if (!empty($historicalSample)) {
            $this->info("\n📋 Dataset Histórico (ENTRENAMIENTO) - Primer registro:");
            $firstHistorical = $historicalSample[0];
            $this->showColumnTypes($firstHistorical);
        }

        if (!empty($currentSample)) {
            $this->info("\n📋 Dataset Actual (PREDICCIÓN) - Primer registro:");
            $firstCurrent = $currentSample[0];
            $this->showColumnTypes($firstCurrent);
        }

        $this->info("\n💡 COMANDOS DISPONIBLES:");
        $this->info("   php artisan lms:generate-local-dropout-dataset --historical --records=500");
        $this->info("   php artisan lms:generate-local-dropout-dataset --current");
        $this->info("   php artisan lms:generate-local-dropout-dataset --all --records=300");
    }

    private function showColumnTypes(array $record)
    {
        $tableData = [];
        foreach ($record as $column => $value) {
            $type = gettype($value);
            $tableData[] = [$column, $type, $value];
        }
        $this->table(['Columna', 'Tipo', 'Valor Ejemplo'], $tableData);
    }
}
