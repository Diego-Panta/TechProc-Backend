<?php
// app/Domains/DataAnalyst/Exports/Local/QuickDashboardExport.php

namespace App\Domains\DataAnalyst\Exports\Local;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuickDashboardExport implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $dashboard = $this->data['dashboard'];
        
        return [
            ['Métrica', 'Valor'],
            ['Total Estudiantes Activos', $dashboard['total_students'] ?? 0],
            ['Total Grupos Activos', $dashboard['total_groups'] ?? 0],
            ['Pagos Pendientes', $dashboard['pending_payments'] ?? 0],
            ['Tickets Abiertos', $dashboard['open_tickets'] ?? 0],
            ['Sesiones Hoy', $dashboard['today_sessions'] ?? 0],
            ['Exámenes Próximos (7 días)', $dashboard['upcoming_exams'] ?? 0],
            ['', ''],
            ['Fecha de Exportación', $this->data['export_date'] ?? ''],
            ['Título del Reporte', $this->data['title'] ?? 'Dashboard Rápido']
        ];
    }

    public function headings(): array
    {
        return [
            'Métrica del Sistema',
            'Valor'
        ];
    }

    public function title(): string
    {
        return 'Dashboard Rápido';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A1:B1' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8F5E8']]],
            'A2:B7' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F9F9F9']]],
            'A9:B10' => ['font' => ['italic' => true]],
        ];
    }
}