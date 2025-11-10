<?php
// database/migrations/2024_01_01_000002_create_data_analytics_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_analytics', function (Blueprint $table) {
            $table->id();
            
            // Relación polimórfica
            $table->string('analyzable_type'); // Enrollment, Group, etc.
            $table->unsignedBigInteger('analyzable_id');
            
            // Tipo de análisis
            $table->string('analysis_type'); // attendance, progress, performance, risk_prediction
            
            // Periodo de análisis
            $table->string('period')->default('30d'); // 7d, 30d, 90d, all
            
            // Métricas comunes
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('rate', 5, 2)->nullable();
            $table->unsignedInteger('total_events')->nullable();
            $table->unsignedInteger('completed_events')->nullable();
            
            // Nivel de riesgo/alerta
            $table->string('risk_level')->nullable();
            
            // Datos específicos del análisis en JSON
            $table->json('metrics')->nullable();
            $table->json('trends')->nullable();
            $table->json('patterns')->nullable();
            $table->json('comparisons')->nullable();
            $table->json('triggers')->nullable();
            $table->json('recommendations')->nullable();
            
            // Metadatos
            $table->timestamp('calculated_at');
            $table->timestamps();

            // Índices individuales con nombres cortos
            $table->index('analyzable_type', 'idx_analytics_type');
            $table->index('analyzable_id', 'idx_analytics_id');
            $table->index('analysis_type', 'idx_analytics_analysis');
            $table->index('period', 'idx_analytics_period');
            $table->index('score', 'idx_analytics_score');
            $table->index('rate', 'idx_analytics_rate');
            $table->index('risk_level', 'idx_analytics_risk');
            
            // Índices compuestos con nombres más cortos
            $table->index(['analysis_type', 'risk_level'], 'idx_type_risk');
            $table->index(['analysis_type', 'score'], 'idx_type_score');
            $table->index(['analyzable_type', 'analyzable_id'], 'idx_analyzable');
            
            // Índice único principal - nombres más cortos para cada columna
            $table->unique([
                'analyzable_type', 
                'analyzable_id', 
                'analysis_type', 
                'period'
            ], 'uniq_analytics_main');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_analytics');
    }
};