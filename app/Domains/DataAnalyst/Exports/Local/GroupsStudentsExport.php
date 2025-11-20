<?php
// app/Domains/DataAnalyst/Exports/Local/GroupsStudentsExport.php

namespace App\Domains\DataAnalyst\Exports\Local;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GroupsStudentsExport implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data['groups']->map(function ($group) {
            return [
                $group->group_id ?? '',
                $group->group_name ?? '',
                $group->course_name ?? '',
                $group->start_date ? \Carbon\Carbon::parse($group->start_date)->format('d/m/Y') : '',
                $group->end_date ? \Carbon\Carbon::parse($group->end_date)->format('d/m/Y') : '',
                $group->group_status ?? '',
                $group->student_id ?? '',
                $group->student_name ?? '',
                $group->student_email ?? '',
                $group->academic_status ?? '',
                $group->payment_status ?? '',
                $group->final_grade ?? '',
                $group->attendance_percentage ? $group->attendance_percentage . '%' : '',
                $group->enrollment_date ? \Carbon\Carbon::parse($group->enrollment_date)->format('d/m/Y') : ''
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID Grupo',
            'Nombre Grupo',
            'Curso',
            'Fecha Inicio',
            'Fecha Fin',
            'Estado Grupo',
            'ID Estudiante',
            'Nombre Estudiante',
            'Email Estudiante',
            'Estado Académico',
            'Estado Pago',
            'Nota Final',
            'Asistencia %',
            'Fecha Matrícula'
        ];
    }

    public function title(): string
    {
        return 'Grupos con Estudiantes';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A1:N1' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF0F5']]],
        ];
    }
}