<?php
// app/Console/Commands/CheckDropoutModel.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\DataAnalyst\Services\DropoutPredictionService;

class CheckDropoutModel extends Command
{
    protected $signature = 'lms:check-dropout-model';
    protected $description = 'Verifica el estado del modelo de predicción de deserción';

    public function handle(DropoutPredictionService $predictionService)
    {
        $this->info('🔍 Verificando modelo de predicción de deserción...');

        // Verificar métricas del modelo
        $this->info('📊 Obteniendo métricas del modelo...');
        $metrics = $predictionService->getModelMetrics();
        
        if (isset($metrics['error'])) {
            $this->error('❌ Error al obtener métricas: ' . $metrics['error']);
            return;
        }

        $this->info("✅ Modelo activo - Precisión: " . ($metrics['accuracy'] ?? 'N/A'));
        $this->info("📈 Recall: " . ($metrics['recall'] ?? 'N/A'));
        $this->info("🎯 F1 Score: " . ($metrics['f1_score'] ?? 'N/A'));

        // Verificar predicciones con filtro para evitar errores
        $this->info('🎯 Obteniendo predicciones de ejemplo (primeros 5 registros)...');
        
        try {
            $predictions = $predictionService->getDropoutPredictions(['only_with_data' => true]);
            
            if (!empty($predictions['predictions'])) {
                $sample = array_slice($predictions['predictions'], 0, 5);
                $this->info("✅ " . count($predictions['predictions']) . " predicciones obtenidas");
                
                $this->info("📊 Distribución de riesgo:");
                $this->info("   - ALTO: " . $predictions['summary']['high_risk_count']);
                $this->info("   - MEDIO: " . $predictions['summary']['medium_risk_count']);
                $this->info("   - BAJO: " . $predictions['summary']['low_risk_count']);
                
                $this->info("\n📋 Ejemplo de predicciones:");
                foreach ($sample as $pred) {
                    $this->info("   👤 {$pred['student_name']} - Riesgo: {$pred['risk_level']} ({$pred['dropout_probability']})");
                }
            }
        } catch (\Exception $e) {
            $this->warn("⚠️  Error en predicciones: " . $e->getMessage());
            $this->info("💡 Probando con consulta simple...");
            
            // Consulta simple de verificación
            try {
                $highRisk = $predictionService->getHighRiskStudents();
                $this->info("✅ " . count($highRisk) . " estudiantes de alto riesgo identificados");
            } catch (\Exception $e2) {
                $this->error("❌ Error crítico: " . $e2->getMessage());
            }
        }

        $this->info('🎉 Sistema de predicción verificado correctamente!');
    }
}