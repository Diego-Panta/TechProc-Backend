<?php
// app/Domains/DataAnalyst/Models/DataAnalytic.php

namespace App\Domains\DataAnalyst\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DataAnalytic extends Model
{
    protected $table = 'data_analytics';

    protected $fillable = [
        'analyzable_type',
        'analyzable_id',
        'analysis_type',
        'period',
        'score',
        'rate',
        'total_events',
        'completed_events',
        'risk_level',
        'metrics',
        'trends',
        'patterns',
        'comparisons',
        'triggers',
        'recommendations',
        'calculated_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'rate' => 'decimal:2',
        'total_events' => 'integer',
        'completed_events' => 'integer',
        'metrics' => 'array',
        'trends' => 'array',
        'patterns' => 'array',
        'comparisons' => 'array',
        'triggers' => 'array',
        'recommendations' => 'array',
        'calculated_at' => 'datetime',
    ];

    /**
     * Relación polimórfica con Enrollment, Group, etc.
     */
    public function analyzable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope para tipo de análisis
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('analysis_type', $type);
    }

    /**
     * Scope para período
     */
    public function scopeOfPeriod($query, string $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Scope para nivel de riesgo
     */
    public function scopeRiskLevel($query, string $level)
    {
        return $query->where('risk_level', $level);
    }

    /**
     * Scope para analizable específico
     */
    public function scopeForAnalyzable($query, string $type, int $id)
    {
        return $query->where('analyzable_type', $type)
                    ->where('analyzable_id', $id);
    }

    /**
     * Verifica si es de alto riesgo
     */
    public function isHighRisk(): bool
    {
        return in_array($this->risk_level, ['critical', 'high']);
    }

    /**
     * Obtiene el estado basado en el score/rate
     */
    public function getStatusAttribute(): string
    {
        $value = $this->score ?? $this->rate ?? 0;

        return match (true) {
            $value >= 90 => 'excellent',
            $value >= 80 => 'good',
            $value >= 70 => 'warning',
            $value >= 60 => 'concerning',
            default => 'critical',
        };
    }

    /**
     * Obtiene métricas específicas
     */
    public function getMetric(string $key, $default = null)
    {
        return $this->metrics[$key] ?? $default;
    }

    /**
     * Obtiene tendencias específicas
     */
    public function getTrend(string $key, $default = null)
    {
        return $this->trends[$key] ?? $default;
    }
}