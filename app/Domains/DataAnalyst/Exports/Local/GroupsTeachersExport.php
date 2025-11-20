<?php
// app/Domains/DataAnalyst/Exports/Local/GroupsTeachersExport.php

namespace App\Domains\DataAnalyst\Exports\Local;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GroupsTeachersExport implements FromCollection, WithTitle, WithHeadings, WithStyles
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
                $group->teacher_name ?? '',
                $group->teacher_email ?? ''
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
            'Estado',
            'Docente',
            'Email Docente'
        ];
    }

    public function title(): string
    {
        return 'Grupos con Docentes';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A1:H1' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E6F7FF']]],
        ];
    }
}