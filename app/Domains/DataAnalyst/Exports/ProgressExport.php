<?php
// app/Domains/DataAnalyst/Exports/ProgressExport.php

namespace App\Domains\DataAnalyst\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProgressExport implements WithMultipleSheets
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [
            new ProgressSummarySheet($this->data),
            new ProgressModuleSheet($this->data),
            new ProgressGradeSheet($this->data),
        ];

        return $sheets;
    }
}

class ProgressSummarySheet implements FromArray, WithTitle, WithStyles
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
            ['REPORTE DE PROGRESO - RESUMEN'],
            ['Fecha de exportación:', $this->data['export_date'] ?? ''],
            [''],
            ['FILTROS APLICADOS'],
            ...$this->formatFilters($filters),
            [''],
            ['MÉTRICAS PRINCIPALES'],
            ['Tasa de completación promedio:', ($summary['avg_completion_rate'] ?? 0) . '%'],
            ['Calificación promedio:', ($summary['avg_grade'] ?? 0)],
            [''],
            ['ESTADÍSTICAS DETALLADAS'],
            ['Total módulos registrados:', count($this->data['module_data'] ?? [])],
            ['Total registros de calificaciones:', count($this->data['grade_data'] ?? [])],
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
            11 => ['font' => ['bold' => true]],
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

class ProgressModuleSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $modules = $this->data['module_data'] ?? [];
        
        $formatted = [];
        foreach ($modules as $module) {
            $formatted[] = [
                $module['user_id'] ?? '',
                $module['student_name'] ?? '',
                $module['student_email'] ?? '',
                $module['group_name'] ?? '',
                $module['course_name'] ?? '',
                $module['course_version'] ?? '',
                $module['module_title'] ?? '',
                $module['module_order'] ?? 0,
                $module['total_sessions'] ?? 0,
                $module['attended_sessions'] ?? 0,
                ($module['completion_rate'] ?? 0) . '%',
                $module['completion_days'] ?? 0,
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
            'Módulo',
            'Orden Módulo',
            'Total Sesiones',
            'Sesiones Atendidas',
            'Tasa Completación',
            'Días para Completar'
        ];
    }

    public function title(): string
    {
        return 'Completación Módulos';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class ProgressGradeSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $grades = $this->data['grade_data'] ?? [];
        
        $formatted = [];
        foreach ($grades as $grade) {
            $formatted[] = [
                $grade['user_id'] ?? '',
                $grade['student_name'] ?? '',
                $grade['student_email'] ?? '',
                $grade['group_name'] ?? '',
                $grade['course_name'] ?? '',
                $grade['course_version'] ?? '',
                $grade['total_grades'] ?? 0,
                $grade['avg_grade'] ?? 0,
                $grade['grade_stddev'] ?? 0,
                $grade['min_grade'] ?? 0,
                $grade['max_grade'] ?? 0,
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
            'Total Calificaciones',
            'Calificación Promedio',
            'Desviación Estándar',
            'Calificación Mínima',
            'Calificación Máxima'
        ];
    }

    public function title(): string
    {
        return 'Consistencia Calificaciones';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}