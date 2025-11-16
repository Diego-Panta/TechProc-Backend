<?php
// app/Domains/DataAnalyst/Exports/Local/GradesSummaryExport.php

namespace App\Domains\DataAnalyst\Exports\Local;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradesSummaryExport implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data['grades']->map(function ($grade) {
            return [
                $grade->group_name ?? '',
                $grade->student_name ?? '',
                $grade->module_title ?? '',
                $grade->exam_title ?? '',
                $grade->grade ?? '',
                $grade->exam_date ? \Carbon\Carbon::parse($grade->exam_date)->format('d/m/Y H:i') : ''
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Grupo',
            'Estudiante',
            'Módulo',
            'Examen',
            'Calificación',
            'Fecha Examen'
        ];
    }

    public function title(): string
    {
        return 'Calificaciones';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A1:F1' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F5F5DC']]],
        ];
    }
}