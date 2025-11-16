<?php
// app/Domains/DataAnalyst/Exports/DropoutPredictionsExport.php

namespace App\Domains\DataAnalyst\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DropoutPredictionsExport implements WithMultipleSheets
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [
            new DropoutPredictionsSummarySheet($this->data),
            new DropoutPredictionsListSheet($this->data),
            new DropoutRiskAnalysisSheet($this->data),
        ];

        return $sheets;
    }
}

class DropoutPredictionsSummarySheet implements FromArray, WithTitle, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $summary = $this->data['summary'] ?? [];
        $filters = $this->data['filters'] ?? [];
        
        return [
            ['REPORTE DE PREDICCIONES DE DESERCIÓN - RESUMEN'],
            ['Fecha de exportación:', $this->data['export_date'] ?? ''],
            [''],
            ['FILTROS APLICADOS'],
            ...$this->formatFilters($filters),
            [''],
            ['MÉTRICAS PRINCIPALES'],
            ['Total de estudiantes:', $summary['total_students'] ?? 0],
            ['Estudiantes de alto riesgo:', $summary['high_risk_count'] ?? 0],
            ['Estudiantes de riesgo medio:', $summary['medium_risk_count'] ?? 0],
            ['Estudiantes de bajo riesgo:', $summary['low_risk_count'] ?? 0],
            ['Probabilidad promedio de deserción:', round(($summary['avg_dropout_probability'] ?? 0) * 100, 1) . '%'],
            [''],
            ['ESTADO DE DATOS'],
            ['Datos completos:', $summary['data_status_summary']['complete_data'] ?? 0],
            ['Faltan datos académicos:', $summary['data_status_summary']['missing_academic'] ?? 0],
            ['Faltan datos de asistencia:', $summary['data_status_summary']['missing_attendance'] ?? 0],
        ];
    }

    public function title(): string
    {
        return 'Resumen';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            4 => ['font' => ['bold' => true]],
            8 => ['font' => ['bold' => true]],
            15 => ['font' => ['bold' => true]],
        ];
    }

    private function formatFilters(array $filters): array
    {
        $formatted = [];
        foreach ($filters as $key => $value) {
            $formatted[] = [ucfirst(str_replace('_', ' ', $key)) . ':', $value];
        }
        return $formatted;
    }
}

class DropoutPredictionsListSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $predictions = $this->data['predictions'] ?? [];
        
        $formatted = [];
        foreach ($predictions as $prediction) {
            $formatted[] = [
                $prediction['enrollment_id'] ?? '',
                $prediction['student_name'] ?? '',
                $prediction['group_name'] ?? '',
                round(($prediction['dropout_probability'] ?? 0) * 100, 1) . '%',
                $prediction['risk_level'] ?? '',
                $prediction['recommended_action'] ?? '',
                $prediction['avg_grade'] ?? 0,
                $prediction['attendance_rate'] ?? 0,
                round(($prediction['payment_regularity'] ?? 0) * 100, 1) . '%',
                $prediction['total_exams_taken'] ?? 0,
                $prediction['data_status'] ?? '',
                $this->formatRiskFactors($prediction),
            ];
        }
        
        return $formatted;
    }

    public function headings(): array
    {
        return [
            'ID Matrícula',
            'Estudiante',
            'Grupo',
            'Probabilidad Deserción',
            'Nivel Riesgo',
            'Acción Recomendada',
            'Nota Promedio',
            'Asistencia (%)',
            'Regularidad Pagos (%)',
            'Exámenes Realizados',
            'Estado Datos',
            'Factores de Riesgo'
        ];
    }

    public function title(): string
    {
        return 'Predicciones';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function formatRiskFactors(array $prediction): string
    {
        $factors = array_filter([
            $prediction['risk_factor_1'] ?? null,
            $prediction['risk_factor_2'] ?? null,
            $prediction['risk_factor_3'] ?? null,
            $prediction['risk_factor_4'] ?? null
        ]);
        
        return implode(', ', $factors);
    }
}

class DropoutRiskAnalysisSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $predictions = $this->data['predictions'] ?? [];
        
        $analysis = $this->analyzeRiskFactors($predictions);
        
        return [
            ['ANÁLISIS DE FACTORES DE RIESGO'],
            [''],
            ['DISTRIBUCIÓN POR NIVEL DE RIESGO'],
            ['Alto riesgo:', $analysis['high_risk_count']],
            ['Riesgo medio:', $analysis['medium_risk_count']],
            ['Bajo riesgo:', $analysis['low_risk_count']],
            [''],
            ['FACTORES DE RIESGO MÁS COMUNES'],
            ...$analysis['common_factors'],
            [''],
            ['ESTADÍSTICAS ACADÉMICAS POR RIESGO'],
            ['Nota promedio - Alto riesgo:', $analysis['avg_grade_high_risk']],
            ['Asistencia promedio - Alto riesgo:', $analysis['avg_attendance_high_risk'] . '%'],
            ['Regularidad pagos - Alto riesgo:', $analysis['avg_payment_high_risk'] . '%'],
        ];
    }

    public function title(): string
    {
        return 'Análisis Riesgo';
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
            8 => ['font' => ['bold' => true]],
            12 => ['font' => ['bold' => true]],
        ];
    }

    private function analyzeRiskFactors(array $predictions): array
    {
        $highRisk = array_filter($predictions, fn($p) => ($p['risk_level'] ?? '') === 'ALTO');
        $mediumRisk = array_filter($predictions, fn($p) => ($p['risk_level'] ?? '') === 'MEDIO');
        $lowRisk = array_filter($predictions, fn($p) => ($p['risk_level'] ?? '') === 'BAJO');

        $factors = [
            'Asistencia baja' => 0,
            'Rendimiento bajo' => 0,
            'Pagos irregulares' => 0,
            'Pago atrasado' => 0
        ];

        foreach ($predictions as $prediction) {
            if (!empty($prediction['risk_factor_1'])) $factors['Asistencia baja']++;
            if (!empty($prediction['risk_factor_2'])) $factors['Rendimiento bajo']++;
            if (!empty($prediction['risk_factor_3'])) $factors['Pagos irregulares']++;
            if (!empty($prediction['risk_factor_4'])) $factors['Pago atrasado']++;
        }

        arsort($factors);

        return [
            'high_risk_count' => count($highRisk),
            'medium_risk_count' => count($mediumRisk),
            'low_risk_count' => count($lowRisk),
            'common_factors' => array_map(fn($k, $v) => [$k, $v], array_keys($factors), array_values($factors)),
            'avg_grade_high_risk' => count($highRisk) ? round(array_sum(array_column($highRisk, 'avg_grade')) / count($highRisk), 1) : 0,
            'avg_attendance_high_risk' => count($highRisk) ? round(array_sum(array_column($highRisk, 'attendance_rate')) / count($highRisk), 1) : 0,
            'avg_payment_high_risk' => count($highRisk) ? round(array_sum(array_column($highRisk, 'payment_regularity')) * 100 / count($highRisk), 1) : 0,
        ];
    }
}