<?php
// app/Domains/DataAnalyst/Exports/AttendanceExport.php

namespace App\Domains\DataAnalyst\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements WithMultipleSheets
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [
            new AttendanceSummarySheet($this->data),
            new AttendanceStudentsSheet($this->data),
            new AttendanceGroupsSheet($this->data),
        ];

        return $sheets;
    }
}

class AttendanceSummarySheet implements FromArray, WithTitle, WithStyles
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
            ['REPORTE DE ASISTENCIA - RESUMEN'],
            ['Fecha de exportación:', $this->data['export_date'] ?? ''],
            [''],
            ['FILTROS APLICADOS'],
            ...$this->formatFilters($filters),
            [''],
            ['MÉTRICAS PRINCIPALES'],
            ['Total de estudiantes:', $summary['total_students'] ?? 0],
            ['Promedio de asistencia:', ($summary['avg_attendance_rate'] ?? 0) . '%'],
            ['Total de sesiones:', $summary['total_sessions'] ?? 0],
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

class AttendanceStudentsSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $students = $this->data['student_data'] ?? [];
        
        $formatted = [];
        foreach ($students as $student) {
            $formatted[] = [
                $student['user_id'] ?? '',
                $student['student_name'] ?? '',
                $student['student_email'] ?? '',
                $student['group_name'] ?? '',
                $student['course_name'] ?? '',
                $student['course_version'] ?? '',
                $student['total_sessions'] ?? 0,
                $student['present_count'] ?? 0,
                $student['absent_count'] ?? 0,
                $student['late_count'] ?? 0,
                ($student['attendance_rate'] ?? 0) . '%',
            ];
        }
        
        return $formatted;
    }

    public function headings(): array
    {
        return [
            'ID Estudiante',
            'Nombre Estudiante',
            'Email',
            'Grupo',
            'Curso',
            'Versión Curso',
            'Total Sesiones',
            'Presente',
            'Ausente',
            'Tardío',
            'Tasa de Asistencia'
        ];
    }

    public function title(): string
    {
        return 'Estudiantes';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class AttendanceGroupsSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $groups = $this->data['group_data'] ?? [];
        
        $formatted = [];
        foreach ($groups as $group) {
            $formatted[] = [
                $group['group_id'] ?? '',
                $group['group_name'] ?? '',
                $group['course_name'] ?? '',
                $group['course_version'] ?? '',
                $group['total_students'] ?? 0,
                ($group['avg_attendance_rate'] ?? 0) . '%',
                ($group['avg_absence_rate'] ?? 0) . '%',
            ];
        }
        
        return $formatted;
    }

    public function headings(): array
    {
        return [
            'ID Grupo',
            'Nombre Grupo',
            'Curso',
            'Versión Curso',
            'Total Estudiantes',
            'Asistencia Promedio',
            'Ausentismo Promedio'
        ];
    }

    public function title(): string
    {
        return 'Grupos';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}