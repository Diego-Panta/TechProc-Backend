<?php
// app/Domains/DataAnalyst/Exports/Local/ActiveStudentsExport.php

namespace App\Domains\DataAnalyst\Exports\Local;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActiveStudentsExport implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data['students']->map(function ($student) {
            return [
                $student->student_id ?? '',
                $student->student_name ?? '',
                $student->student_email ?? '',
                $student->group_name ?? '',
                $student->course_name ?? '',
                $student->academic_status ?? '',
                $student->payment_status ?? '',
                $student->final_grade ?? '',
                $student->attendance_percentage ? $student->attendance_percentage . '%' : '',
                $student->enrollment_status ?? '',
                $student->enrollment_date ? \Carbon\Carbon::parse($student->enrollment_date)->format('d/m/Y') : ''
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID Estudiante',
            'Nombre',
            'Email',
            'Grupo',
            'Curso',
            'Estado Académico',
            'Estado Pago',
            'Nota Final',
            'Asistencia %',
            'Estado Matrícula',
            'Fecha Matrícula'
        ];
    }

    public function title(): string
    {
        return 'Estudiantes Activos';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A1:K1' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E6E6FA']]],
        ];
    }
}