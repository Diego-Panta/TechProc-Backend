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
            new AttendanceChartsSheet($this->data),
        ];

        return $sheets;
    }
}

class AttendanceChartsSheet implements FromArray, WithTitle, WithStyles
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
            ['ANÁLISIS GRÁFICO - DISTRIBUCIÓN DE ASISTENCIA'],
            [''],
        ];

        // Distribución de estados de asistencia - NUEVA ESTRUCTURA
        $statusData = $charts['status_distribution']['status_distribution'] ?? [];
        if (!empty($statusData)) {
            $output[] = ['DISTRIBUCIÓN DE ESTADOS DE ASISTENCIA (AGRUPADO POR GRUPO)'];
            $output[] = ['Grupo', 'Curso', 'Estado', 'Cantidad', 'Porcentaje'];
            
            foreach ($statusData as $groupData) {
                $groupName = $groupData['group_name'] ?? '';
                $courseName = $groupData['course_name'] ?? '';
                
                foreach ($groupData['statuses'] ?? [] as $status) {
                    $output[] = [
                        $groupName,
                        $courseName,
                        $status['status'] ?? '',
                        $status['count'] ?? 0,
                        ($status['percentage'] ?? 0) . '%'
                    ];
                }
                $output[] = ['', '', '', '', '']; // Línea separadora entre grupos
            }
            $output[] = [''];
            
            // Resumen de distribución
            $summary = $charts['status_distribution']['summary'] ?? [];
            if (!empty($summary)) {
                $output[] = ['RESUMEN DISTRIBUCIÓN'];
                $output[] = ['Total registros:', $summary['total_records'] ?? 0];
                $output[] = ['Total grupos:', $summary['total_groups'] ?? 0];
                $output[] = [''];
            }
        }

        // Tendencia semanal de ausencias - NUEVA ESTRUCTURA
        $weeklyData = $charts['weekly_absence_trends']['weekly_trends'] ?? [];
        if (!empty($weeklyData)) {
            $output[] = ['TENDENCIA SEMANAL DE AUSENCIAS'];
            $output[] = ['Semana', 'Total Registros', 'Sesiones Únicas', 'Estudiantes', 'Ausencias', 'Tasa Ausentismo'];
            
            foreach ($weeklyData as $week) {
                $output[] = [
                    $week['week_label'] ?? '',
                    $week['total_attendance_records'] ?? 0,
                    $week['unique_sessions'] ?? 0,
                    $week['total_students'] ?? 0,
                    $week['absence_count'] ?? 0,
                    ($week['absence_rate'] ?? 0) . '%'
                ];
            }
            $output[] = [''];
        }

        // Calendario de asistencia (resumen)
        $calendarData = $charts['attendance_calendar']['attendance_calendar'] ?? [];
        if (!empty($calendarData)) {
            $output[] = ['CALENDARIO DE ASISTENCIA - RESUMEN POR FECHA'];
            $output[] = ['Fecha', 'Estudiante', 'Estado', 'Grupo', 'Sesiones'];
            
            // Limitar a los primeros 50 registros para no saturar Excel
            $limitedData = array_slice($calendarData, 0, 50);
            
            foreach ($limitedData as $record) {
                $output[] = [
                    $record['fecha'] ?? '',
                    $record['student_name'] ?? '',
                    $record['status'] ?? '',
                    $record['group_name'] ?? '',
                    $record['session_count'] ?? 0
                ];
            }
            
            if (count($calendarData) > 50) {
                $output[] = ['... y ' . (count($calendarData) - 50) . ' registros más'];
            }
        }

        // Información de filtros aplicados en gráficas
        $filtersApplied = $charts['status_distribution']['filters_applied'] ?? [];
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

        // Distribución de estados
        $statusData = $charts['status_distribution']['status_distribution'] ?? [];
        if (!empty($statusData)) {
            $sectionTitleRow = $currentRow + 2;
            $styles[$sectionTitleRow] = ['font' => ['bold' => true, 'size' => 12]];
            
            // Calcular filas totales para distribución
            $distributionRows = 2; // Headers
            foreach ($statusData as $groupData) {
                $distributionRows += count($groupData['statuses'] ?? []) + 1; // +1 para línea separadora
            }
            $distributionRows += 2; // Resumen
            
            $currentRow += $distributionRows;
        }

        // Tendencia semanal
        $weeklyData = $charts['weekly_absence_trends']['weekly_trends'] ?? [];
        if (!empty($weeklyData)) {
            $sectionTitleRow = $currentRow + 1;
            $styles[$sectionTitleRow] = ['font' => ['bold' => true, 'size' => 12]];
            $currentRow += 2 + count($weeklyData) + 1;
        }

        // Calendario
        $calendarData = $charts['attendance_calendar']['attendance_calendar'] ?? [];
        if (!empty($calendarData)) {
            $sectionTitleRow = $currentRow + 1;
            $styles[$sectionTitleRow] = ['font' => ['bold' => true, 'size' => 12]];
        }

        return $styles;
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
        
        // Información adicional del nuevo servicio
        $filtersApplied = $this->data['filters_applied'] ?? [];
        $dataRangeInfo = $this->data['data_range_info'] ?? [];
        
        return [
            ['REPORTE DE ASISTENCIA - RESUMEN'],
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
            ['Total de estudiantes:', $summary['total_students'] ?? 0],
            ['Total de grupos:', $summary['total_groups'] ?? 0],
            ['Promedio de asistencia:', ($summary['avg_attendance_rate'] ?? 0) . '%'],
            ['Total de sesiones:', $summary['total_sessions'] ?? 0],
            ['Total presente:', $summary['total_present'] ?? 0],
            ['Total ausente:', $summary['total_absent'] ?? 0],
            ['Total tardío:', $summary['total_late'] ?? 0],
            [''],
            ['INFORMACIÓN DE GRÁFICAS INCLUIDAS'],
            ['Distribución de estados:', !empty($this->data['charts']['status_distribution']) ? 'SÍ' : 'NO'],
            ['Tendencia semanal:', !empty($this->data['charts']['weekly_absence_trends']) ? 'SÍ' : 'NO'],
            ['Calendario de asistencia:', !empty($this->data['charts']['attendance_calendar']) ? 'SÍ' : 'NO'],
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
            22 => ['font' => ['bold' => true]],
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

// Las clases AttendanceStudentsSheet y AttendanceGroupsSheet se mantienen igual
// ya que la estructura de datos principales no cambió
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