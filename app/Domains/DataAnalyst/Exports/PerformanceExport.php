<?php
// app/Domains/DataAnalyst/Exports/PerformanceExport.php

namespace App\Domains\DataAnalyst\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PerformanceExport implements WithMultipleSheets
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [
            new PerformanceSummarySheet($this->data),
            new PerformanceStudentsSheet($this->data),
            new PerformanceCoursesSheet($this->data),
        ];

        return $sheets;
    }
}

class PerformanceSummarySheet implements FromArray, WithTitle, WithStyles
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
            ['REPORTE DE RENDIMIENTO - RESUMEN'],
            ['Fecha de exportación:', $this->data['export_date'] ?? ''],
            [''],
            ['FILTROS APLICADOS'],
            ...$this->formatFilters($filters),
            [''],
            ['MÉTRICAS PRINCIPALES'],
            ['Total estudiantes:', $summary['total_students'] ?? 0],
            ['Total cursos/grupos:', $summary['total_courses'] ?? 0],
            ['Tasa de aprobación general:', ($summary['overall_approval_rate'] ?? 0) . '%'],
            ['Calificación final promedio:', $summary['overall_avg_grade'] ?? 0],
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

class PerformanceStudentsSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $students = $this->data['student_performance'] ?? [];
        
        $formatted = [];
        foreach ($students as $student) {
            $formatted[] = [
                $student['user_id'] ?? '',
                $student['student_name'] ?? '',
                $student['student_email'] ?? '',
                $student['group_name'] ?? '',
                $student['course_name'] ?? '',
                $student['course_version'] ?? '',
                $student['final_grade'] ?? 'N/A',
                ($student['attendance_percentage'] ?? 0) . '%',
                $student['enrollment_status'] ?? '',
                $student['total_exams_taken'] ?? 0,
                $student['overall_avg_grade'] ?? 'N/A',
                $student['min_grade'] ?? 'N/A',
                $student['max_grade'] ?? 'N/A',
                $student['grade_stddev'] ?? 'N/A',
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
            'Calificación Final',
            'Porcentaje Asistencia',
            'Estado Matrícula',
            'Total Exámenes',
            'Promedio General',
            'Calificación Mínima',
            'Calificación Máxima',
            'Desviación Estándar'
        ];
    }

    public function title(): string
    {
        return 'Rendimiento Estudiantes';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class PerformanceCoursesSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $courses = $this->data['course_performance'] ?? [];
        
        $formatted = [];
        foreach ($courses as $course) {
            $formatted[] = [
                $course['group_name'] ?? '',
                $course['course_name'] ?? '',
                $course['course_version'] ?? '',
                $course['total_students'] ?? 0,
                $course['avg_final_grade'] ?? 'N/A',
                ($course['avg_attendance'] ?? 0) . '%',
                $course['approved_students'] ?? 0,
                ($course['approval_rate'] ?? 0) . '%',
            ];
        }
        
        return $formatted;
    }

    public function headings(): array
    {
        return [
            'Grupo',
            'Curso',
            'Versión Curso',
            'Total Estudiantes',
            'Calificación Final Promedio',
            'Asistencia Promedio',
            'Estudiantes Aprobados',
            'Tasa de Aprobación'
        ];
    }

    public function title(): string
    {
        return 'Rendimiento Cursos';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}