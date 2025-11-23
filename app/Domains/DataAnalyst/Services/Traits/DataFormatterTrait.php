<?php
// app/Domains/DataAnalyst/Services/Traits/DataFormatterTrait.php

namespace App\Domains\DataAnalyst\Services\Traits;

trait DataFormatterTrait
{
    /**
     * Extrae datos de la fila manejando diferentes formatos
     */
    private function extractDataFromRow(array $row): array
    {
        $data = $row['data'] ?? [];
        
        if (is_string($data)) {
            return json_decode($data, true) ?? [];
        }
        
        if (is_array($data)) {
            return $data;
        }
        
        return [];
    }

    /**
     * Formatea fechas de BigQuery
     */
    private function formatBigQueryDate($dateValue): string
    {
        if ($dateValue instanceof \Google\Cloud\BigQuery\Date) {
            return $dateValue->formatAsString();
        }
        
        if ($dateValue instanceof \Google\Cloud\BigQuery\Timestamp) {
            return $dateValue->get()->format('Y-m-d');
        }
        
        if (is_string($dateValue)) {
            return $dateValue;
        }
        
        if ($dateValue instanceof \DateTime) {
            return $dateValue->format('Y-m-d');
        }
        
        return (string) $dateValue;
    }

    /**
     * Calcula correlación entre dos arrays de datos
     */
    private function calculateCorrelation(array $data): float
    {
        if (count($data) < 2) {
            return 0.0;
        }

        $attendanceRates = array_column($data, 'attendance_rate');
        $grades = array_column($data, 'avg_grade');

        $n = count($attendanceRates);
        $sumX = array_sum($attendanceRates);
        $sumY = array_sum($grades);
        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $attendanceRates[$i] * $grades[$i];
            $sumX2 += $attendanceRates[$i] * $attendanceRates[$i];
            $sumY2 += $grades[$i] * $grades[$i];
        }

        $numerator = ($n * $sumXY) - ($sumX * $sumY);
        $denominator = sqrt(($n * $sumX2 - $sumX * $sumX) * ($n * $sumY2 - $sumY * $sumY));

        return $denominator == 0 ? 0.0 : round($numerator / $denominator, 4);
    }
}