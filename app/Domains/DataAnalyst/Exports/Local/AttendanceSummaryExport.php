<?php
// app/Domains/DataAnalyst/Exports/Local/AttendanceSummaryExport.php

namespace App\Domains\DataAnalyst\Exports\Local;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceSummaryExport implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data['attendance']->map(function ($record) {
            return [
                $record->group_name ?? '',
                $record->student_name ?? '',
                $record->total_sessions ?? 0,
                $record->present_count ?? 0,
                $record->absent_count ?? 0,
                $record->late_count ?? 0,
                $record->attendance_rate ? $record->attendance_rate . '%' : '0%'
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Grupo',
            'Estudiante',
            'Total Sesiones',
            'Presente',
            'Ausente',
            'Tardío',
            'Tasa Asistencia'
        ];
    }

    public function title(): string
    {
        return 'Asistencia';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A1:G1' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F0FFF0']]],
        ];
    }
}