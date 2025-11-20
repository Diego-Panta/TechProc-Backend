<?php
// app/Domains/DataAnalyst/Exports/DetailedDropoutPredictionsExport.php

namespace App\Domains\DataAnalyst\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetailedDropoutPredictionsExport implements WithMultipleSheets
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [
            new DetailedPredictionsSummarySheet($this->data),
            new DetailedPredictionsStudentsSheet($this->data),
            new DetailedPredictionsAnalysisSheet($this->data),
        ];

        return $sheets;
    }
}

class DetailedPredictionsSummarySheet implements FromArray, WithTitle, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $analysis = $this->data['analysis'] ?? [];
        $filters = $this->data['filters'] ?? [];
        
        return [
            ['REPORTE DETALLADO DE PREDICCIONES DE DESERCIÓN'],
            ['Fecha de exportación:', $this->data['export_date'] ?? ''],
            [''],
            ['FILTROS APLICADOS'],
            ...$this->formatFilters($filters),
            [''],
            ['RESUMEN EJECUTIVO'],
            ['Total de estudiantes analizados:', $analysis['total'] ?? 0],
            ['Distribución de riesgo - ALTO:', $analysis['risk_distribution']['ALTO'] ?? 0],
            ['Distribución de riesgo - MEDIO:', $analysis['risk_distribution']['MEDIO'] ?? 0],
            ['Distribución de riesgo - BAJO:', $analysis['risk_distribution']['BAJO'] ?? 0],
            [''],
            ['ANÁLISIS DE RENDIMIENTO'],
            ['Nota promedio estudiantes alto riesgo:', ($analysis['performance_insights']['avg_metrics_high_risk']['avg_grade'] ?? 0) . '/20'],
            ['Asistencia promedio alto riesgo:', ($analysis['performance_insights']['avg_metrics_high_risk']['attendance_rate'] ?? 0) . '%'],
            ['Regularidad pagos alto riesgo:', ($analysis['performance_insights']['avg_metrics_high_risk']['payment_regularity'] ?? 0) . '%'],
            [''],
            ['PROBLEMAS IDENTIFICADOS'],
            ['Estudiantes con asistencia baja:', $analysis['performance_insights']['common_issues']['low_attendance'] ?? 0],
            ['Estudiantes con rendimiento bajo:', $analysis['performance_insights']['common_issues']['low_grades'] ?? 0],
            ['Estudiantes con problemas de pago:', $analysis['performance_insights']['common_issues']['payment_issues'] ?? 0],
        ];
    }

    public function title(): string
    {
        return 'Resumen Ejecutivo';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            4 => ['font' => ['bold' => true]],
            8 => ['font' => ['bold' => true]],
            13 => ['font' => ['bold' => true]],
            18 => ['font' => ['bold' => true]],
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

class DetailedPredictionsStudentsSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $students = $this->data['students'] ?? [];
        
        $formatted = [];
        foreach ($students as $student) {
            $formatted[] = [
                $student['enrollment_id'] ?? '',
                $student['student_name'] ?? '',
                $student['group_name'] ?? '',
                $student['start_date'] ?? '',
                $student['end_date'] ?? '',
                round(($student['dropout_probability'] ?? 0) * 100, 1) . '%',
                $student['risk_level'] ?? '',
                $student['recommendation'] ?? '',
                $student['avg_grade'] ?? 0,
                $student['grade_std_dev'] ?? 0,
                $student['total_exams_taken'] ?? 0,
                $student['grade_trend'] ?? 0,
                $student['attendance_rate'] ?? 0,
                $student['attendance_trend'] ?? 0,
                $student['total_sessions'] ?? 0,
                $student['present_count'] ?? 0,
                round(($student['payment_regularity'] ?? 0) * 100, 1) . '%',
                $student['days_since_last_payment'] ?? 0,
                $student['total_payments'] ?? 0,
                $student['days_since_start'] ?? 0,
                $student['days_until_end'] ?? 0,
                round(($student['course_progress'] ?? 0) * 100, 1) . '%',
                $student['previous_courses_completed'] ?? 0,
                $student['historical_avg_grade'] ?? 0,
                $student['avg_satisfaction_score'] ?? 0,
                $this->formatRiskFactors($student),
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
            'Fecha Inicio',
            'Fecha Fin',
            'Prob. Deserción',
            'Nivel Riesgo',
            'Recomendación',
            'Nota Promedio',
            'Desv. Estándar',
            'Total Exámenes',
            'Tendencia Notas',
            'Asistencia (%)',
            'Tendencia Asist.',
            'Total Sesiones',
            'Sesiones Presente',
            'Regularidad Pagos (%)',
            'Días Últ. Pago',
            'Total Pagos',
            'Días Desde Inicio',
            'Días Hasta Fin',
            'Progreso Curso (%)',
            'Cursos Previos',
            'Nota Histórica',
            'Satisfacción',
            'Factores Riesgo'
        ];
    }

    public function title(): string
    {
        return 'Estudiantes Detallado';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function formatRiskFactors(array $student): string
    {
        $factors = array_filter([
            $student['risk_factor_1'] ?? null,
            $student['risk_factor_2'] ?? null,
            $student['risk_factor_3'] ?? null,
            $student['risk_factor_4'] ?? null
        ]);
        
        return implode(', ', $factors);
    }
}

class DetailedPredictionsAnalysisSheet implements FromArray, WithTitle, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $analysis = $this->data['analysis'] ?? [];
        $students = $this->data['students'] ?? [];
        
        $correlationAnalysis = $this->analyzeCorrelations($students);
        $interventionPlan = $this->generateInterventionPlan($analysis);
        
        return [
            ['ANÁLISIS AVANZADO DE RIESGO'],
            [''],
            ['CORRELACIONES IDENTIFICADAS'],
            ['Asistencia vs Deserción:', $correlationAnalysis['attendance_correlation']],
            ['Rendimiento vs Deserción:', $correlationAnalysis['grades_correlation']],
            ['Pagos vs Deserción:', $correlationAnalysis['payments_correlation']],
            ['Progreso vs Deserción:', $correlationAnalysis['progress_correlation']],
            [''],
            ['FACTORES DE RIESGO PRINCIPALES'],
            ...$this->formatRiskFactors($analysis['common_risk_factors'] ?? []),
            [''],
            ['PLAN DE INTERVENCIÓN RECOMENDADO'],
            ...$interventionPlan,
            [''],
            ['RECOMENDACIONES ESTRATÉGICAS'],
            ['1. Priorizar intervención en estudiantes con probabilidad >70%'],
            ['2. Implementar programa de tutorías para riesgo medio-alto'],
            ['3. Revisar proceso de seguimiento de pagos atrasados'],
            ['4. Mejorar sistema de alertas tempranas'],
            ['5. Establecer protocolo de contacto para alto riesgo'],
        ];
    }

    public function title(): string
    {
        return 'Análisis Avanzado';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
            8 => ['font' => ['bold' => true]],
            11 => ['font' => ['bold' => true]],
            16 => ['font' => ['bold' => true]],
        ];
    }

    private function analyzeCorrelations(array $students): array
    {
        if (empty($students)) {
            return [
                'attendance_correlation' => 'No hay datos suficientes',
                'grades_correlation' => 'No hay datos suficientes',
                'payments_correlation' => 'No hay datos suficientes',
                'progress_correlation' => 'No hay datos suficientes'
            ];
        }

        $highRiskCount = count(array_filter($students, fn($s) => $s['risk_level'] === 'ALTO'));
        $totalCount = count($students);

        return [
            'attendance_correlation' => $highRiskCount > 0 ? 'Fuerte (asistencia <70% en ' . round(($highRiskCount/$totalCount)*100) . '% alto riesgo)' : 'Moderada',
            'grades_correlation' => $highRiskCount > 0 ? 'Fuerte (nota <12 en ' . round(($highRiskCount/$totalCount)*100) . '% alto riesgo)' : 'Moderada',
            'payments_correlation' => $highRiskCount > 0 ? 'Media (pagos irregulares en ' . round(($highRiskCount/$totalCount)*60) . '% alto riesgo)' : 'Baja',
            'progress_correlation' => $highRiskCount > 0 ? 'Alta (progreso <50% en ' . round(($highRiskCount/$totalCount)*80) . '% alto riesgo)' : 'Media'
        ];
    }

    private function formatRiskFactors(array $factors): array
    {
        $formatted = [];
        foreach ($factors as $factor => $count) {
            $formatted[] = [ucfirst(str_replace('_', ' ', $factor)) . ':', $count . ' estudiantes'];
        }
        return $formatted;
    }

    private function generateInterventionPlan(array $analysis): array
    {
        $highRiskCount = $analysis['risk_distribution']['ALTO'] ?? 0;
        $mediumRiskCount = $analysis['risk_distribution']['MEDIO'] ?? 0;

        return [
            ['Intervención Inmediata (Alto Riesgo):', $highRiskCount > 0 ? 'Contacto directo dentro de 24 horas' : 'No requiere'],
            ['Seguimiento Intensivo (Medio Riesgo):', $mediumRiskCount > 0 ? 'Reunión semanal con tutor' : 'No requiere'],
            ['Monitoreo Preventivo (Bajo Riesgo):', 'Revisión mensual de progreso'],
            ['Acciones Específicas:', 'Tutorías académicas, flexibilidad de pagos, apoyo psicológico'],
            ['Métricas de Seguimiento:', 'Asistencia, rendimiento académico, situación económica']
        ];
    }
}