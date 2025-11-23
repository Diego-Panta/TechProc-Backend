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
            new PerformanceChartsSheet($this->data),
        ];

        return $sheets;
    }
}

class PerformanceChartsSheet implements FromArray, WithTitle, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $charts = $this->data['charts'] ?? [];
        
        $output = [
            ['ANÁLISIS GRÁFICO - RENDIMIENTO ACADÉMICO'],
            [''],
        ];

        // Distribución de calificaciones
        $gradeDistribution = $charts['grade_distribution']['grade_distribution'] ?? [];
        if (!empty($gradeDistribution)) {
            $output[] = ['DISTRIBUCIÓN DE CALIFICACIONES'];
            $output[] = ['Rango', 'Estado', 'Cantidad Estudiantes'];
            
            foreach ($gradeDistribution as $distribution) {
                $output[] = [
                    $distribution['grade_range'] ?? '',
                    $distribution['status'] ?? '',
                    $distribution['student_count'] ?? 0
                ];
            }
            $output[] = [''];
            
            // Estadísticas adicionales
            $stats = $charts['grade_distribution']['statistics'] ?? [];
            if (!empty($stats)) {
                $output[] = ['ESTADÍSTICAS DE CALIFICACIONES'];
                $output[] = ['Total Estudiantes:', $stats['total_students'] ?? 0];
                $output[] = ['Tasa de Aprobación:', ($stats['approval_rate'] ?? 0) . '%'];
                $output[] = ['Calificación Promedio:', $stats['avg_grade'] ?? 0];
                $output[] = [''];
            }
        }

        // Correlación asistencia-calificación
        $correlationData = $charts['attendance_grade_correlation']['scatter_data'] ?? [];
        if (!empty($correlationData)) {
            $output[] = ['CORRELACIÓN ASISTENCIA VS CALIFICACIÓN'];
            $output[] = ['Estudiante', 'Grupo', 'Asistencia (%)', 'Calificación Promedio', 'Estado Académico', 'Total Exámenes'];
            
            foreach (array_slice($correlationData, 0, 20) as $student) {
                $output[] = [
                    $student['student_name'] ?? '',
                    $student['group_name'] ?? '',
                    $student['attendance_rate'] ?? 0,
                    $student['avg_grade'] ?? 0,
                    $student['academic_status'] ?? '',
                    $student['total_exams'] ?? 0
                ];
            }
            $output[] = [''];
            
            // Información de correlación
            $correlation = $charts['attendance_grade_correlation']['correlation'] ?? 0;
            $approvalStats = $charts['attendance_grade_correlation']['approval_stats'] ?? [];
            $summary = $charts['attendance_grade_correlation']['summary'] ?? [];
            
            $output[] = ['ANÁLISIS DE CORRELACIÓN'];
            $output[] = ['Coeficiente de Correlación:', $correlation];
            $output[] = ['Estudiantes Aprobados:', $approvalStats['approved'] ?? 0];
            $output[] = ['Estudiantes Reprobados:', $approvalStats['failed'] ?? 0];
            $output[] = ['Tasa de Aprobación:', ($approvalStats['approval_rate'] ?? 0) . '%'];
            $output[] = ['Asistencia Promedio:', ($summary['avg_attendance'] ?? 0) . '%'];
            $output[] = ['Calificación Promedio:', $summary['avg_grade'] ?? 0];
            $output[] = ['Nota Mínima Aprobatoria:', $summary['passing_grade'] ?? 11];
            $output[] = [''];
        }

        // Rendimiento por grupo (Radar simplificado)
        $groupPerformance = $charts['group_performance_radar']['group_performance'] ?? [];
        if (!empty($groupPerformance)) {
            $output[] = ['RENDIMIENTO POR GRUPO - ANÁLISIS COMPARATIVO'];
            $output[] = ['Grupo', 'Curso', 'Total Estudiantes', 'Calificación Promedio', 'Asistencia Promedio', 'Tasa Aprobación', 'Puntuación Desempeño'];
            
            foreach ($groupPerformance as $group) {
                $output[] = [
                    $group['group_name'] ?? '',
                    $group['course_name'] ?? '',
                    $group['total_students'] ?? 0,
                    $group['avg_final_grade'] ?? 0,
                    ($group['avg_attendance'] ?? 0) . '%',
                    ($group['approval_rate'] ?? 0) . '%',
                    ($group['performance_score'] ?? 0) . '%'
                ];
            }
        }

        // Información de filtros aplicados en gráficas
        $filtersApplied = $charts['grade_distribution']['filters_applied'] ?? [];
        if (!empty($filtersApplied)) {
            $output[] = [''];
            $output[] = ['FILTROS APLICADOS EN GRÁFICAS:'];
            foreach ($filtersApplied as $filter) {
                $output[] = [$filter];
            }
        }

        return $output;
    }

    public function title(): string
    {
        return 'Gráficas y Análisis';
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];

        $charts = $this->data['charts'] ?? [];
        $currentRow = 1;

        // Distribución de calificaciones
        $gradeDistribution = $charts['grade_distribution']['grade_distribution'] ?? [];
        if (!empty($gradeDistribution)) {
            $sectionTitleRow = $currentRow + 2;
            $styles[$sectionTitleRow] = ['font' => ['bold' => true, 'size' => 12]];
            
            // Título de estadísticas
            $statsTitleRow = $sectionTitleRow + 2 + count($gradeDistribution) + 1;
            $styles[$statsTitleRow] = ['font' => ['bold' => true, 'size' => 12]];
            
            $currentRow = $statsTitleRow + 6; // 6 filas de estadísticas
        }

        // Correlación
        $correlationData = $charts['attendance_grade_correlation']['scatter_data'] ?? [];
        if (!empty($correlationData)) {
            $sectionTitleRow = $currentRow + 1;
            $styles[$sectionTitleRow] = ['font' => ['bold' => true, 'size' => 12]];
            
            // Título de análisis de correlación
            $analysisTitleRow = $sectionTitleRow + 2 + min(20, count($correlationData)) + 1;
            $styles[$analysisTitleRow] = ['font' => ['bold' => true, 'size' => 12]];
            
            $currentRow = $analysisTitleRow + 8; // 8 filas de análisis
        }

        // Rendimiento por grupo
        $groupPerformance = $charts['group_performance_radar']['group_performance'] ?? [];
        if (!empty($groupPerformance)) {
            $sectionTitleRow = $currentRow + 1;
            $styles[$sectionTitleRow] = ['font' => ['bold' => true, 'size' => 12]];
        }

        return $styles;
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
        
        // Información adicional del nuevo servicio
        $filtersApplied = $this->data['filters_applied'] ?? [];
        $dataScope = $this->data['data_scope'] ?? [];
        
        return [
            ['REPORTE DE RENDIMIENTO - RESUMEN'],
            ['Fecha de exportación:', $this->data['export_date'] ?? ''],
            [''],
            ['FILTROS APLICADOS EN DATOS PRINCIPALES'],
            ...$this->formatFilters($filters),
            [''],
            ['RESUMEN DE FILTROS APLICADOS'],
            ...array_map(fn($filter) => [$filter], $filtersApplied),
            [''],
            ['INFORMACIÓN DEL ALCANCE DE DATOS'],
            ['Filtros de fecha aplicados:', $dataScope['date_filters_applied'] ? 'SÍ' : 'NO'],
            ['Alcance:', $dataScope['scope'] ?? 'No especificado'],
            [''],
            ['MÉTRICAS PRINCIPALES'],
            ['Total de estudiantes:', $summary['total_students'] ?? 0],
            ['Total de cursos/grupos:', $summary['total_courses'] ?? 0],
            ['Tasa de aprobación general:', ($summary['overall_approval_rate'] ?? 0) . '%'],
            ['Calificación final promedio:', $summary['overall_avg_grade'] ?? 0],
            ['Asistencia promedio general:', ($summary['overall_avg_attendance'] ?? 0) . '%'],
            ['Verificación de consistencia:', $summary['data_consistency_check'] ?? 'No verificada'],
            [''],
            ['FILTROS DE SEGURIDAD APLICADOS'],
            ['Estado del grupo:', $summary['filters_applied']['group_status'] ?? 'active'],
            ['Estado académico:', $summary['filters_applied']['academic_status'] ?? 'active'],
            ['Estado de pago:', $summary['filters_applied']['payment_status'] ?? 'paid'],
            ['Tiene calificaciones:', $summary['filters_applied']['has_grades'] ? 'SÍ' : 'NO'],
            [''],
            ['INFORMACIÓN DE GRÁFICAS INCLUIDAS'],
            ['Distribución de calificaciones:', !empty($this->data['charts']['grade_distribution']) ? 'SÍ' : 'NO'],
            ['Correlación asistencia-calificación:', !empty($this->data['charts']['attendance_grade_correlation']) ? 'SÍ' : 'NO'],
            ['Rendimiento por grupo:', !empty($this->data['charts']['group_performance_radar']) ? 'SÍ' : 'NO'],
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
            14 => ['font' => ['bold' => true]],
            19 => ['font' => ['bold' => true]],
            25 => ['font' => ['bold' => true]],
        ];
    }

    private function formatFilters(array $filters): array
    {
        $formatted = [];
        foreach ($filters as $key => $value) {
            $formatted[] = [ucfirst(str_replace('_', ' ', $key)) . ':', $value];
        }
        return empty($formatted) ? [['Sin filtros aplicados']] : $formatted;
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