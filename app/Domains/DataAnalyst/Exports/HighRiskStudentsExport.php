<?php
// app/Domains/DataAnalyst/Exports/HighRiskStudentsExport.php

namespace App\Domains\DataAnalyst\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class HighRiskStudentsExport implements WithMultipleSheets
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [
            new HighRiskSummarySheet($this->data),
            new HighRiskStudentsSheet($this->data),
            new HighRiskActionPlanSheet($this->data),
        ];

        return $sheets;
    }
}

class HighRiskSummarySheet implements FromArray, WithTitle, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $students = $this->data['high_risk_students'] ?? [];
        $filters = $this->data['filters'] ?? [];
        
        $analysis = $this->analyzeHighRiskStudents($students);
        
        return [
            ['🚨 REPORTE DE ESTUDIANTES DE ALTO RIESGO - INTERVENCIÓN INMEDIATA'],
            ['Fecha de exportación:', $this->data['export_date'] ?? ''],
            [''],
            ['ALERTA CRÍTICA'],
            ['Total estudiantes alto riesgo:', count($students)],
            ['Probabilidad promedio:', $analysis['avg_probability'] . '%'],
            ['Rango de probabilidad:', $analysis['min_probability'] . '% - ' . $analysis['max_probability'] . '%'],
            [''],
            ['CARACTERÍSTICAS PRINCIPALES'],
            ['Nota promedio:', $analysis['avg_grade'] . '/20'],
            ['Asistencia promedio:', $analysis['avg_attendance'] . '%'],
            ['Regularidad pagos promedio:', $analysis['avg_payment'] . '%'],
            ['Días promedio último pago:', $analysis['avg_days_since_payment']],
            [''],
            ['URGENCIA DE ACCIÓN'],
            ['Se requiere intervención inmediata en todos los casos'],
            ['Contacto debe realizarse dentro de las próximas 24 horas'],
            ['Asignar tutor académico para seguimiento personalizado'],
            ['Revisar situación económica y ofrecer flexibilidad'],
        ];
    }

    public function title(): string
    {
        return 'Alerta Crítica';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FF0000']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE0E0']]
            ],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF0F0']]
            ],
            8 => ['font' => ['bold' => true]],
            12 => ['font' => ['bold' => true]],
            16 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF0F0']]
            ],
        ];
    }

    private function analyzeHighRiskStudents(array $students): array
    {
        if (empty($students)) {
            return [
                'avg_probability' => 0,
                'min_probability' => 0,
                'max_probability' => 0,
                'avg_grade' => 0,
                'avg_attendance' => 0,
                'avg_payment' => 0,
                'avg_days_since_payment' => 0
            ];
        }

        $probabilities = array_column($students, 'dropout_probability');
        $grades = array_column($students, 'avg_grade');
        $attendance = array_column($students, 'attendance_rate');
        $payments = array_column($students, 'payment_regularity');
        $daysSincePayment = array_column($students, 'days_since_last_payment');

        return [
            'avg_probability' => round(array_sum($probabilities) / count($probabilities) * 100, 1),
            'min_probability' => round(min($probabilities) * 100, 1),
            'max_probability' => round(max($probabilities) * 100, 1),
            'avg_grade' => round(array_sum($grades) / count($grades), 1),
            'avg_attendance' => round(array_sum($attendance) / count($attendance), 1),
            'avg_payment' => round((array_sum($payments) / count($payments)) * 100, 1),
            'avg_days_since_payment' => round(array_sum($daysSincePayment) / count($daysSincePayment), 1)
        ];
    }
}

class HighRiskStudentsSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $students = $this->data['high_risk_students'] ?? [];
        
        $formatted = [];
        foreach ($students as $student) {
            $formatted[] = [
                $student['enrollment_id'] ?? '',
                $student['student_name'] ?? '',
                $student['group_name'] ?? '',
                round(($student['dropout_probability'] ?? $student['riesgo_porcentaje'] ?? 0), 1) . '%',
                $student['avg_grade'] ?? 0,
                $student['attendance_rate'] ?? 0,
                round(($student['payment_regularity'] ?? 0) * 100, 1) . '%',
                $student['days_since_last_payment'] ?? 0,
                $student['total_exams_taken'] ?? 0,
                $student['total_sessions'] ?? 0,
                $student['accion_recomendada'] ?? $student['recommended_action'] ?? 'INTERVENCIÓN INMEDIATA',
                $this->getUrgencyLevel($student),
                $this->generateContactPriority($student),
            ];
        }
        
        // Ordenar por probabilidad descendente
        usort($formatted, fn($a, $b) => floatval($b[3]) <=> floatval($a[3]));
        
        return $formatted;
    }

    public function headings(): array
    {
        return [
            'ID Matrícula',
            'Estudiante',
            'Grupo',
            'Probabilidad',
            'Nota Prom.',
            'Asistencia (%)',
            'Pagos (%)',
            'Días Últ. Pago',
            'Total Exámenes',
            'Total Sesiones',
            'Acción Inmediata',
            'Nivel Urgencia',
            'Prioridad Contacto'
        ];
    }

    public function title(): string
    {
        return 'Estudiantes Críticos';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF6B6B']]
            ],
        ];
    }

    private function getUrgencyLevel(array $student): string
    {
        $probability = $student['dropout_probability'] ?? $student['riesgo_porcentaje'] ?? 0;
        
        if ($probability >= 80) return '🚨 CRÍTICO';
        if ($probability >= 70) return '⚠️ ALTO';
        return '🔴 MEDIO';
    }

    private function generateContactPriority(array $student): string
    {
        $probability = $student['dropout_probability'] ?? $student['riesgo_porcentaje'] ?? 0;
        $attendance = $student['attendance_rate'] ?? 0;
        $paymentDelay = $student['days_since_last_payment'] ?? 0;

        if ($probability >= 80 || $attendance < 50 || $paymentDelay > 60) {
            return 'INMEDIATA (24h)';
        }
        if ($probability >= 70 || $attendance < 70 || $paymentDelay > 30) {
            return 'ALTA (48h)';
        }
        return 'MEDIA (72h)';
    }
}

class HighRiskActionPlanSheet implements FromArray, WithTitle, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $students = $this->data['high_risk_students'] ?? [];
        
        $actionPlan = $this->generateActionPlan($students);
        
        return [
            ['PLAN DE ACCIÓN PARA INTERVENCIÓN INMEDIATA'],
            [''],
            ['PROTOCOLO DE CONTACTO INMEDIATO'],
            ['1. Contactar vía telefónica como primer recurso'],
            ['2. Seguimiento por email si no hay respuesta en 4 horas'],
            ['3. Contactar referente familiar si persiste sin respuesta'],
            ['4. Asignar tutor académico para seguimiento personalizado'],
            [''],
            ['ACCIONES ESPECÍFICAS POR ÁREA'],
            ...$actionPlan['specific_actions'],
            [''],
            ['CRONOGRAMA DE SEGUIMIENTO'],
            ...$actionPlan['followup_schedule'],
            [''],
            ['MÉTRICAS DE ÉXITO DE LA INTERVENCIÓN'],
            ['• Mejora en asistencia: Objetivo +20% en 2 semanas'],
            ['• Mejora en rendimiento: Objetivo +2 puntos en nota promedio'],
            ['• Regularización de pagos: Objetivo 100% en situación actual'],
            ['• Reducción probabilidad: Objetivo -15% en 30 días'],
        ];
    }

    public function title(): string
    {
        return 'Plan de Acción';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
            9 => ['font' => ['bold' => true]],
            14 => ['font' => ['bold' => true]],
            18 => ['font' => ['bold' => true]],
        ];
    }

    private function generateActionPlan(array $students): array
    {
        return [
            'specific_actions' => [
                ['Área Académica:', 'Tutorías personalizadas, flexibilidad en entregas, material de apoyo'],
                ['Área Económica:', 'Planes de pago flexibles, becas parciales, asesoría financiera'],
                ['Área Psicológica:', 'Sesiones de consejería, grupos de apoyo, seguimiento emocional'],
                ['Área Administrativa:', 'Flexibilidad en horarios, extensiones de plazo, permisos especiales'],
            ],
            'followup_schedule' => [
                ['Primera semana:', 'Contacto diario, evaluación inicial, establecimiento de metas'],
                ['Segunda semana:', 'Seguimiento cada 2 días, ajuste de estrategias'],
                ['Tercera semana:', 'Seguimiento semanal, evaluación de progreso'],
                ['Cuarta semana:', 'Evaluación final, ajuste de nivel de riesgo'],
            ]
        ];
    }
}