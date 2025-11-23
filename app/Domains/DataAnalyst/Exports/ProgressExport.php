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
            new ProgressChartsSheet($this->data),
        ];

        return $sheets;
    }
}

class ProgressChartsSheet implements FromArray, WithTitle, WithStyles
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
            ['ANÁLISIS GRÁFICO - EVOLUCIÓN DEL PROGRESO'],
            [''],
        ];

        // Evolución de calificaciones - INCLUYENDO FILTROS APLICADOS
        $gradeEvolution = $charts['grade_evolution']['grade_evolution'] ?? [];
        if (!empty($gradeEvolution)) {
            $output[] = ['EVOLUCIÓN DE CALIFICACIONES - REGISTROS RECIENTES'];
            $output[] = ['Fecha Examen', 'Estudiante', 'Grupo', 'Examen', 'Módulo', 'Calificación'];
            
            // Agrupar por estudiante para mejor análisis
            $students = [];
            foreach ($gradeEvolution as $record) {
                $studentName = $record['student_name'] ?? '';
                if (!isset($students[$studentName])) {
                    $students[$studentName] = [];
                }
                $students[$studentName][] = $record;
            }
            
            // Mostrar máximo 10 estudiantes con sus evoluciones
            $studentCount = 0;
            foreach ($students as $studentName => $records) {
                if ($studentCount >= 5) break; // Limitar a 5 estudiantes
                
                $output[] = ['', '', '', '', '', ''];
                $output[] = ['ESTUDIANTE:', $studentName, 'GRUPO:', $records[0]['group_name'] ?? '', '', ''];
                $output[] = ['Fecha', 'Examen', 'Módulo', 'Calificación', 'Tendencia', ''];
                
                $previousGrade = null;
                foreach (array_slice($records, 0, 8) as $record) { // Máximo 8 exámenes por estudiante
                    $trend = '';
                    if ($previousGrade !== null) {
                        if ($record['grade'] > $previousGrade) {
                            $trend = '📈 Mejoró';
                        } elseif ($record['grade'] < $previousGrade) {
                            $trend = '📉 Bajó';
                        } else {
                            $trend = '➡️ Igual';
                        }
                    } else {
                        $trend = '🆕 Inicial';
                    }
                    
                    $output[] = [
                        $record['exam_date'] ?? '',
                        $record['exam_title'] ?? '',
                        $record['module_title'] ?? '',
                        $record['grade'] ?? 0,
                        $trend,
                        ''
                    ];
                    
                    $previousGrade = $record['grade'];
                }
                
                $studentCount++;
            }
            
            // Estadísticas generales de evolución
            $output[] = ['', '', '', '', '', ''];
            $output[] = ['RESUMEN ESTADÍSTICO - EVOLUCIÓN DE CALIFICACIONES'];
            $output[] = ['Total registros:', count($gradeEvolution)];
            $output[] = ['Estudiantes únicos:', count($students)];
            $output[] = ['Promedio de exámenes por estudiante:', count($gradeEvolution) / max(1, count($students))];
            
            // Calcular tendencia general
            if (count($gradeEvolution) > 1) {
                $firstGrades = [];
                $lastGrades = [];
                
                foreach ($students as $studentRecords) {
                    if (count($studentRecords) > 1) {
                        $firstGrades[] = $studentRecords[0]['grade'] ?? 0;
                        $lastGrades[] = end($studentRecords)['grade'] ?? 0;
                    }
                }
                
                if (!empty($firstGrades) && !empty($lastGrades)) {
                    $avgFirst = array_sum($firstGrades) / count($firstGrades);
                    $avgLast = array_sum($lastGrades) / count($lastGrades);
                    $improvement = $avgLast - $avgFirst;
                    
                    $output[] = ['Mejora promedio:', number_format($improvement, 2) . ' puntos'];
                    $output[] = ['Tendencia general:', $improvement > 0 ? '📈 Mejorando' : ($improvement < 0 ? '📉 Decreciendo' : '➡️ Estable')];
                }
            }
        } else {
            $output[] = ['No hay datos disponibles para la evolución de calificaciones'];
            $output[] = ['Verifique los filtros aplicados o la disponibilidad de datos'];
        }

        // Información de filtros aplicados en gráficas
        $filtersApplied = $charts['grade_evolution']['filters_applied'] ?? [];
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
        return 'Evolución y Análisis';
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];

        $charts = $this->data['charts'] ?? [];
        $currentRow = 1;

        $gradeEvolution = $charts['grade_evolution']['grade_evolution'] ?? [];
        if (!empty($gradeEvolution)) {
            // Título de evolución
            $sectionTitleRow = $currentRow + 2;
            $styles[$sectionTitleRow] = ['font' => ['bold' => true, 'size' => 12]];
            
            // Título de resumen estadístico
            $students = [];
            foreach ($gradeEvolution as $record) {
                $studentName = $record['student_name'] ?? '';
                if (!isset($students[$studentName])) {
                    $students[$studentName] = [];
                }
                $students[$studentName][] = $record;
            }
            
            // Calcular fila del resumen
            $summaryRow = $sectionTitleRow + 2; // Headers
            $studentCount = 0;
            foreach ($students as $studentName => $records) {
                if ($studentCount >= 5) break;
                $summaryRow += 2 + min(8, count($records)) + 1; // Título estudiante + headers + registros + línea vacía
                $studentCount++;
            }
            
            $styles[$summaryRow] = ['font' => ['bold' => true, 'size' => 12]];
        }

        return $styles;
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
        
        // Información adicional del nuevo servicio
        $filtersApplied = $this->data['filters_applied'] ?? [];
        $dataRangeInfo = $this->data['data_range_info'] ?? [];
        
        return [
            ['REPORTE DE PROGRESO - RESUMEN'],
            ['Fecha de exportación:', $this->data['export_date'] ?? ''],
            [''],
            ['FILTROS APLICADOS EN DATOS PRINCIPALES'],
            ...$this->formatFilters($filters),
            [''],
            ['RESUMEN DE FILTROS APLICADOS'],
            ...array_map(fn($filter) => [$filter], $filtersApplied),
            [''],
            ['INFORMACIÓN DEL RANGO DE DATOS'],
            ['Filtros de fecha aplicados:', $dataRangeInfo['date_filters_applied'] ? 'SÍ' : 'NO'],
            ['Alcance:', $dataRangeInfo['scope'] ?? 'No especificado'],
            [''],
            ['MÉTRICAS PRINCIPALES'],
            ['Tasa de completación promedio:', ($summary['avg_completion_rate'] ?? 0) . '%'],
            ['Calificación promedio:', ($summary['avg_grade'] ?? 0)],
            ['Total estudiantes únicos:', $summary['total_students'] ?? 0],
            ['Total módulos registrados:', $summary['total_modules'] ?? 0],
            ['Total calificaciones registradas:', $summary['total_grades'] ?? 0],
            [''],
            ['INFORMACIÓN DE GRÁFICAS INCLUIDAS'],
            ['Evolución de calificaciones:', !empty($this->data['charts']['grade_evolution']) ? 'SÍ' : 'NO'],
            ['Notas:', 'La evolución muestra el progreso académico a lo largo del tiempo'],
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
            20 => ['font' => ['bold' => true]],
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

// Las clases ProgressModuleSheet y ProgressGradeSheet se mantienen igual
// ya que la estructura de datos principales no cambió
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