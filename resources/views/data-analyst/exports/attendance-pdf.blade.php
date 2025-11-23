{{-- resources/views/data-analyst/exports/attendance-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .summary { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #007bff; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .table th { background-color: #343a40; color: white; font-weight: bold; }
        .filters { margin-bottom: 20px; padding: 15px; background: #e9ecef; border-radius: 5px; }
        .section-title { background: #007bff; color: white; padding: 8px; margin: 20px 0 10px 0; border-radius: 3px; }
        .page-break { page-break-after: always; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bg-success { background-color: #d4edda !important; }
        .bg-warning { background-color: #fff3cd !important; }
        .bg-danger { background-color: #f8d7da !important; }
        .chart-section { margin-bottom: 25px; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; }
        .chart-title { background: #28a745; color: white; padding: 6px; margin: -15px -15px 10px -15px; border-radius: 3px 3px 0 0; }
        .mini-table { width: 100%; border-collapse: collapse; font-size: 9px; }
        .mini-table th, .mini-table td { border: 1px solid #ddd; padding: 4px; text-align: left; }
        .mini-table th { background-color: #6c757d; color: white; }
        .status-present { background-color: #d4edda; }
        .status-absent { background-color: #f8d7da; }
        .status-late { background-color: #fff3cd; }
        .group-section { margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 3px; border-left: 4px solid #6f42c1; }
        .info-box { background: #e3f2fd; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #2196f3; }
        .metric-card { background: white; padding: 10px; border-radius: 5px; border: 1px solid #dee2e6; text-align: center; }
        .metric-value { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .metric-label { font-size: 9px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; color: #343a40;">{{ $title }}</h1>
        <p style="margin: 5px 0; color: #6c757d;">Generado el: {{ $export_date }}</p>
        <p style="margin: 0; color: #6c757d; font-size: 9px;">
            Sistema de Análisis de Asistencia - Reporte Completo
        </p>
    </div>

    <!-- Información de Filtros -->
    <div class="filters">
        <h3 style="margin-top: 0; color: #495057;">Filtros Aplicados</h3>
        
        @if(!empty($filters))
        <div style="margin-bottom: 15px;">
            <h4 style="margin: 0 0 8px 0; color: #6c757d; font-size: 11px;">Filtros Principales:</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                @foreach($filters as $key => $value)
                    <div style="background: white; padding: 8px; border-radius: 3px; border-left: 3px solid #007bff;">
                        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> 
                        <span>{{ $value ?: 'No especificado' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($filters_applied))
        <div style="margin-bottom: 15px;">
            <h4 style="margin: 0 0 8px 0; color: #6c757d; font-size: 11px;">Resumen de Filtros:</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 8px;">
                @foreach($filters_applied as $filter)
                    <div style="background: white; padding: 8px; border-radius: 3px; border-left: 3px solid #28a745;">
                        {{ $filter }}
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($data_range_info))
        <div>
            <h4 style="margin: 0 0 8px 0; color: #6c757d; font-size: 11px;">Información del Rango:</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                <div style="background: white; padding: 8px; border-radius: 3px; border-left: 3px solid #ffc107;">
                    <strong>Filtros de fecha:</strong> 
                    {{ $data_range_info['date_filters_applied'] ? 'Aplicados' : 'No aplicados' }}
                </div>
                <div style="background: white; padding: 8px; border-radius: 3px; border-left: 3px solid #17a2b8;">
                    <strong>Alcance:</strong> {{ $data_range_info['scope'] ?? 'Todos los datos' }}
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Resumen General -->
    @if(!empty($summary))
    <div class="summary">
        <h3 style="margin-top: 0; color: #007bff;">Resumen General de Asistencia</h3>
        
        <!-- Métricas Principales -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 15px;">
            <div class="metric-card">
                <div class="metric-value" style="color: #007bff;">{{ $summary['total_students'] ?? 0 }}</div>
                <div class="metric-label">Total Estudiantes</div>
            </div>
            <div class="metric-card">
                <div class="metric-value" style="color: #28a745;">{{ $summary['avg_attendance_rate'] ?? 0 }}%</div>
                <div class="metric-label">Asistencia Promedio</div>
            </div>
            <div class="metric-card">
                <div class="metric-value" style="color: #6c757d;">{{ $summary['total_sessions'] ?? 0 }}</div>
                <div class="metric-label">Total Sesiones</div>
            </div>
            <div class="metric-card">
                <div class="metric-value" style="color: #dc3545;">{{ $summary['total_groups'] ?? 0 }}</div>
                <div class="metric-label">Grupos Activos</div>
            </div>
        </div>

        <!-- Desglose de Estados -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
            <div class="metric-card" style="background: #d4edda;">
                <div class="metric-value" style="color: #155724;">{{ $summary['total_present'] ?? 0 }}</div>
                <div class="metric-label">Presente</div>
            </div>
            <div class="metric-card" style="background: #f8d7da;">
                <div class="metric-value" style="color: #721c24;">{{ $summary['total_absent'] ?? 0 }}</div>
                <div class="metric-label">Ausente</div>
            </div>
            <div class="metric-card" style="background: #fff3cd;">
                <div class="metric-value" style="color: #856404;">{{ $summary['total_late'] ?? 0 }}</div>
                <div class="metric-label">Tardío</div>
            </div>
            <div class="metric-card" style="background: #e2e3e5;">
                <div class="metric-value" style="color: #383d41;">
                    @php
                        $totalRecords = ($summary['total_present'] ?? 0) + ($summary['total_absent'] ?? 0) + ($summary['total_late'] ?? 0);
                    @endphp
                    {{ $totalRecords }}
                </div>
                <div class="metric-label">Total Registros</div>
            </div>
        </div>

        <!-- Estadísticas Adicionales -->
        <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 3px;">
            <h4 style="margin: 0 0 8px 0; color: #6c757d; font-size: 10px;">Estadísticas Adicionales</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; font-size: 9px;">
                <div>
                    <strong>Tasa de Ausentismo:</strong> 
                    {{ $summary['total_sessions'] > 0 ? round(($summary['total_absent'] / $summary['total_sessions']) * 100, 2) : 0 }}%
                </div>
                <div>
                    <strong>Eficiencia de Asistencia:</strong> 
                    {{ $summary['total_students'] > 0 ? round(($summary['total_present'] / ($summary['total_students'] * ($summary['total_sessions'] / max($summary['total_students'], 1)))) * 100, 2) : 0 }}%
                </div>
                <div>
                    <strong>Promedio Sesiones/Estudiante:</strong> 
                    {{ $summary['total_students'] > 0 ? round($summary['total_sessions'] / $summary['total_students'], 1) : 0 }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- SECCIÓN DE GRÁFICAS -->
    @if(!empty($charts))
    <div class="section-title">
        <h3 style="margin: 0; font-size: 14px;">Análisis Gráfico de Asistencia</h3>
    </div>

    <!-- Distribución de Estados de Asistencia -->
    @if(!empty($charts['status_distribution']['status_distribution']))
    <div class="chart-section">
        <div class="chart-title">
            <h4 style="margin: 0; font-size: 12px;">Distribución de Estados de Asistencia por Grupo</h4>
        </div>
        
        @foreach($charts['status_distribution']['status_distribution'] as $groupData)
        <div class="group-section">
            <h5 style="margin: 0 0 8px 0; color: #6f42c1;">
                {{ $groupData['group_name'] }} - {{ $groupData['course_name'] }}
            </h5>
            <table class="mini-table">
                <thead>
                    <tr>
                        <th>Estado</th>
                        <th>Cantidad</th>
                        <th>Porcentaje</th>
                        <th>Proporción</th>
                        <th>Análisis</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupData['statuses'] as $status)
                    <tr class="status-{{ strtolower($status['status'] ?? '') }}">
                        <td>
                            <strong>
                                @if($status['status'] == 'present')
                                    Presente
                                @elseif($status['status'] == 'absent')
                                    Ausente
                                @elseif($status['status'] == 'late')
                                    Tardío
                                @else
                                    {{ $status['status'] }}
                                @endif
                            </strong>
                        </td>
                        <td class="text-center">{{ $status['count'] }}</td>
                        <td class="text-center">{{ $status['percentage'] }}%</td>
                        <td>
                            <div style="background: #e9ecef; height: 12px; border-radius: 2px; position: relative;">
                                <div style="background: 
                                    @if($status['status'] == 'present') #28a745
                                    @elseif($status['status'] == 'absent') #dc3545
                                    @elseif($status['status'] == 'late') #ffc107
                                    @else #6c757d @endif; 
                                    height: 100%; width: {{ $status['percentage'] }}%; border-radius: 2px;">
                                </div>
                                <div style="position: absolute; top: 0; left: 5px; font-size: 8px; color: #000; font-weight: bold;">
                                    {{ $status['percentage'] }}%
                                </div>
                            </div>
                        </td>
                        <td class="text-center" style="font-size: 8px;">
                            @if($status['status'] == 'present' && $status['percentage'] >= 80)
                                <span style="color: #28a745;">Excelente</span>
                            @elseif($status['status'] == 'present' && $status['percentage'] >= 60)
                                <span style="color: #ffc107;">Bueno</span>
                            @elseif($status['status'] == 'absent' && $status['percentage'] > 20)
                                <span style="color: #dc3545;">Crítico</span>
                            @elseif($status['status'] == 'absent' && $status['percentage'] > 10)
                                <span style="color: #fd7e14;">Alerta</span>
                            @else
                                <span style="color: #6c757d;">Normal</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach

        @if(!empty($charts['status_distribution']['summary']))
        @php $chartSummary = $charts['status_distribution']['summary']; @endphp
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin-top: 15px;">
            <div style="text-align: center; background: #e3f2fd; padding: 8px; border-radius: 3px;">
                <div style="font-weight: bold; color: #1976d2; font-size: 14px;">{{ $chartSummary['total_records'] ?? 0 }}</div>
                <div style="font-size: 8px;">Total Registros</div>
            </div>
            <div style="text-align: center; background: #e8f5e8; padding: 8px; border-radius: 3px;">
                <div style="font-weight: bold; color: #2e7d32; font-size: 14px;">{{ $chartSummary['total_groups'] ?? 0 }}</div>
                <div style="font-size: 8px;">Grupos Analizados</div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Tendencia Semanal de Ausencias -->
    @if(!empty($charts['weekly_absence_trends']['weekly_trends']))
    <div class="chart-section">
        <div class="chart-title">
            <h4 style="margin: 0; font-size: 12px;">Tendencia Semanal de Ausencias</h4>
        </div>
        
        <table class="mini-table">
            <thead>
                <tr>
                    <th>Semana</th>
                    <th>Registros</th>
                    <th>Sesiones</th>
                    <th>Estudiantes</th>
                    <th>Ausencias</th>
                    <th>Tasa</th>
                    <th>Tendencia</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $previousRate = null;
                    $weeksData = array_slice($charts['weekly_absence_trends']['weekly_trends'], 0, 8);
                @endphp
                @foreach($weeksData as $week)
                <tr>
                    <td><strong>{{ $week['week_label'] }}</strong></td>
                    <td class="text-center">{{ $week['total_attendance_records'] ?? 0 }}</td>
                    <td class="text-center">{{ $week['unique_sessions'] ?? 0 }}</td>
                    <td class="text-center">{{ $week['total_students'] ?? 0 }}</td>
                    <td class="text-center" style="font-weight: bold; color: #dc3545;">{{ $week['absence_count'] ?? 0 }}</td>
                    <td class="text-center" style="font-weight: bold; 
                        @if($week['absence_rate'] <= 5) color: #28a745;
                        @elseif($week['absence_rate'] <= 15) color: #ffc107;
                        @else color: #dc3545;
                        @endif">
                        {{ $week['absence_rate'] }}%
                    </td>
                    <td class="text-center">
                        @if($previousRate !== null)
                            @if($week['absence_rate'] > $previousRate + 2)
                                <span style="color: #dc3545;">Subiendo</span>
                            @elseif($week['absence_rate'] < $previousRate - 2)
                                <span style="color: #28a745;">Bajando</span>
                            @else
                                <span style="color: #6c757d;">Estable</span>
                            @endif
                        @else
                            <span style="color: #6c757d;">Inicial</span>
                        @endif
                    </td>
                </tr>
                @php
                    $previousRate = $week['absence_rate'];
                @endphp
                @endforeach
            </tbody>
        </table>

        <!-- Análisis de Tendencia -->
        @if(count($weeksData) >= 2)
        @php
            $firstWeek = $weeksData[0];
            $lastWeek = end($weeksData);
            $trend = $lastWeek['absence_rate'] - $firstWeek['absence_rate'];
        @endphp
        <div style="margin-top: 10px; padding: 8px; background: #f8f9fa; border-radius: 3px;">
            <div style="font-size: 9px; color: #6c757d;">
                <strong>Análisis de Tendencia:</strong> 
                @if($trend > 2)
                    <span style="color: #dc3545;">Tendencia al alza (+{{ number_format($trend, 1) }}%)</span>
                @elseif($trend < -2)
                    <span style="color: #28a745;">Tendencia a la baja ({{ number_format($trend, 1) }}%)</span>
                @else
                    <span style="color: #6c757d;">Tendencia estable ({{ number_format($trend, 1) }}%)</span>
                @endif
                | Del {{ $firstWeek['week_label'] }} al {{ $lastWeek['week_label'] }}
            </div>
        </div>
        @endif

        @if(count($charts['weekly_absence_trends']['weekly_trends']) > 8)
        <p style="margin: 5px 0; font-size: 8px; color: #6c757d; text-align: center;">
            <em>Mostrando 8 de {{ count($charts['weekly_absence_trends']['weekly_trends']) }} semanas</em>
        </p>
        @endif
    </div>
    @endif

    <!-- Calendario de Asistencia -->
    @if(!empty($charts['attendance_calendar']['attendance_calendar']))
    <div class="chart-section">
        <div class="chart-title">
            <h4 style="margin: 0; font-size: 12px;">Calendario de Asistencia - Registros Recientes</h4>
        </div>
        
        <table class="mini-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Estudiante</th>
                    <th>Estado</th>
                    <th>Grupo</th>
                    <th>Sesiones</th>
                    <th>Evaluación</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($charts['attendance_calendar']['attendance_calendar'], 0, 20) as $record)
                <tr class="status-{{ strtolower($record['status'] ?? '') }}">
                    <td style="font-weight: bold;">{{ $record['fecha'] ?? 'N/A' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($record['student_name'] ?? '', 18) }}</td>
                    <td class="text-center">
                        <span style="font-weight: bold; padding: 2px 6px; border-radius: 3px;
                            @if($record['status'] == 'present') 
                                background: #d4edda; color: #155724;
                            @elseif($record['status'] == 'absent') 
                                background: #f8d7da; color: #721c24;
                            @elseif($record['status'] == 'late') 
                                background: #fff3cd; color: #856404;
                            @endif">
                            {{ $record['status'] }}
                        </span>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($record['group_name'] ?? '', 15) }}</td>
                    <td class="text-center">{{ $record['session_count'] ?? 0 }}</td>
                    <td class="text-center">
                        @if($record['status'] == 'present')
                            <span style="color: #28a745;">Bueno</span>
                        @elseif($record['status'] == 'absent')
                            <span style="color: #dc3545;">Ausente</span>
                        @else
                            <span style="color: #ffc107;">Tardío</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if(count($charts['attendance_calendar']['attendance_calendar']) > 20)
        <p style="margin: 5px 0; font-size: 8px; color: #6c757d; text-align: center;">
            <em>Mostrando 20 de {{ count($charts['attendance_calendar']['attendance_calendar']) }} registros</em>
        </p>
        @endif

        <!-- Resumen del Calendario -->
        @php
            $calendarData = $charts['attendance_calendar']['attendance_calendar'];
            $presentCount = count(array_filter($calendarData, function($r) { return $r['status'] == 'present'; }));
            $absentCount = count(array_filter($calendarData, function($r) { return $r['status'] == 'absent'; }));
            $lateCount = count(array_filter($calendarData, function($r) { return $r['status'] == 'late'; }));
            $totalCalendar = count($calendarData);
        @endphp
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 5px; margin-top: 10px;">
            <div style="text-align: center; background: #d4edda; padding: 5px; border-radius: 3px;">
                <div style="font-weight: bold; color: #155724; font-size: 10px;">{{ $presentCount }}</div>
                <div style="font-size: 7px;">Presente</div>
            </div>
            <div style="text-align: center; background: #f8d7da; padding: 5px; border-radius: 3px;">
                <div style="font-weight: bold; color: #721c24; font-size: 10px;">{{ $absentCount }}</div>
                <div style="font-size: 7px;">Ausente</div>
            </div>
            <div style="text-align: center; background: #fff3cd; padding: 5px; border-radius: 3px;">
                <div style="font-weight: bold; color: #856404; font-size: 10px;">{{ $lateCount }}</div>
                <div style="font-size: 7px;">Tardío</div>
            </div>
            <div style="text-align: center; background: #e2e3e5; padding: 5px; border-radius: 3px;">
                <div style="font-weight: bold; color: #383d41; font-size: 10px;">{{ $totalCalendar }}</div>
                <div style="font-size: 7px;">Total</div>
            </div>
        </div>
    </div>
    @endif
    @endif

    <!-- DATOS POR ESTUDIANTE -->
    @if(!empty($student_data))
    <div class="page-break"></div>
    
    <div class="section-title">
        <h3 style="margin: 0; font-size: 14px;">Datos por Estudiante ({{ count($student_data) }} estudiantes)</h3>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Estudiante</th>
                <th>Email</th>
                <th>Grupo</th>
                <th>Curso</th>
                <th>Total Sesiones</th>
                <th>Presente</th>
                <th>Ausente</th>
                <th>Tardío</th>
                <th>Tasa Asistencia</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($student_data as $student)
            <tr>
                <td>{{ $student['user_id'] }}</td>
                <td style="font-weight: bold;">{{ $student['student_name'] }}</td>
                <td style="font-size: 9px;">{{ $student['student_email'] }}</td>
                <td>{{ $student['group_name'] }}</td>
                <td style="font-size: 9px;">{{ $student['course_name'] }}</td>
                <td class="text-center">{{ $student['total_sessions'] }}</td>
                <td class="text-center bg-success" style="font-weight: bold;">{{ $student['present_count'] }}</td>
                <td class="text-center bg-danger" style="font-weight: bold;">{{ $student['absent_count'] }}</td>
                <td class="text-center bg-warning" style="font-weight: bold;">{{ $student['late_count'] }}</td>
                <td class="text-center" style="font-weight: bold; 
                    @if($student['attendance_rate'] >= 90) color: #28a745; background: #d4edda;
                    @elseif($student['attendance_rate'] >= 80) color: #ffc107; background: #fff3cd;
                    @elseif($student['attendance_rate'] >= 70) color: #fd7e14; background: #ffe5d0;
                    @else color: #dc3545; background: #f8d7da;
                    @endif">
                    {{ $student['attendance_rate'] }}%
                </td>
                <td class="text-center">
                    @if($student['attendance_rate'] >= 90)
                        <span style="color: #28a745;">Excelente</span>
                    @elseif($student['attendance_rate'] >= 80)
                        <span style="color: #ffc107;">Bueno</span>
                    @elseif($student['attendance_rate'] >= 70)
                        <span style="color: #fd7e14;">Regular</span>
                    @else
                        <span style="color: #dc3545;">Crítico</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Resumen de Estudiantes -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 15px;">
        @php
            $excellentStudents = count(array_filter($student_data, function($s) { return $s['attendance_rate'] >= 90; }));
            $goodStudents = count(array_filter($student_data, function($s) { return $s['attendance_rate'] >= 80 && $s['attendance_rate'] < 90; }));
            $regularStudents = count(array_filter($student_data, function($s) { return $s['attendance_rate'] >= 70 && $s['attendance_rate'] < 80; }));
            $criticalStudents = count(array_filter($student_data, function($s) { return $s['attendance_rate'] < 70; }));
        @endphp
        <div style="text-align: center; background: #d4edda; padding: 10px; border-radius: 5px;">
            <div style="font-weight: bold; color: #155724; font-size: 16px;">{{ $excellentStudents }}</div>
            <div style="font-size: 9px; color: #155724;">Excelente (≥90%)</div>
        </div>
        <div style="text-align: center; background: #fff3cd; padding: 10px; border-radius: 5px;">
            <div style="font-weight: bold; color: #856404; font-size: 16px;">{{ $goodStudents }}</div>
            <div style="font-size: 9px; color: #856404;">Bueno (80-89%)</div>
        </div>
        <div style="text-align: center; background: #ffe5d0; padding: 10px; border-radius: 5px;">
            <div style="font-weight: bold; color: #fd7e14; font-size: 16px;">{{ $regularStudents }}</div>
            <div style="font-size: 9px; color: #fd7e14;">Regular (70-79%)</div>
        </div>
        <div style="text-align: center; background: #f8d7da; padding: 10px; border-radius: 5px;">
            <div style="font-weight: bold; color: #721c24; font-size: 16px;">{{ $criticalStudents }}</div>
            <div style="font-size: 9px; color: #721c24;">Crítico (<70%)</div>
        </div>
    </div>
    @endif

    <!-- DATOS POR GRUPO -->
    @if(!empty($group_data))
    <div class="page-break"></div>
    
    <div class="section-title">
        <h3 style="margin: 0; font-size: 14px;">Datos por Grupo ({{ count($group_data) }} grupos)</h3>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID Grupo</th>
                <th>Nombre Grupo</th>
                <th>Curso</th>
                <th>Versión</th>
                <th>Total Estudiantes</th>
                <th>Asistencia Promedio</th>
                <th>Ausentismo Promedio</th>
                <th>Desempeño</th>
            </tr>
        </thead>
        <tbody>
            @foreach($group_data as $group)
            <tr>
                <td>{{ $group['group_id'] }}</td>
                <td style="font-weight: bold;">{{ $group['group_name'] }}</td>
                <td>{{ $group['course_name'] }}</td>
                <td>{{ $group['course_version'] }}</td>
                <td class="text-center">{{ $group['total_students'] }}</td>
                <td class="text-center" style="font-weight: bold; 
                    @if($group['avg_attendance_rate'] >= 90) color: #28a745; background: #d4edda;
                    @elseif($group['avg_attendance_rate'] >= 80) color: #ffc107; background: #fff3cd;
                    @elseif($group['avg_attendance_rate'] >= 70) color: #fd7e14; background: #ffe5d0;
                    @else color: #dc3545; background: #f8d7da;
                    @endif">
                    {{ $group['avg_attendance_rate'] }}%
                </td>
                <td class="text-center" style="font-weight: bold; color: #dc3545;">
                    {{ $group['avg_absence_rate'] }}%
                </td>
                <td class="text-center">
                    @if($group['avg_attendance_rate'] >= 90)
                        <span style="color: #28a745;">Excelente</span>
                    @elseif($group['avg_attendance_rate'] >= 80)
                        <span style="color: #ffc107;">Bueno</span>
                    @elseif($group['avg_attendance_rate'] >= 70)
                        <span style="color: #fd7e14;">Regular</span>
                    @else
                        <span style="color: #dc3545;">Necesita Mejora</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- PIE DE PÁGINA -->
    <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px; text-align: center;">
        <p style="margin: 0; color: #6c757d; font-size: 9px;">
            Reporte generado automáticamente por el Sistema de Análisis de Asistencia
        </p>
        <p style="margin: 5px 0 0 0; color: #6c757d; font-size: 8px;">
            Fecha de generación: {{ $export_date }}
        </p>
    </div>

    @if(empty($student_data) && empty($group_data) && empty($charts))
    <div style="text-align: center; padding: 40px; color: #6c757d;">
        <h3>No hay datos disponibles</h3>
        <p>No se encontraron registros de asistencia con los filtros aplicados.</p>
        <p style="font-size: 9px; margin-top: 10px;">
            Verifique los filtros o contacte al administrador del sistema.
        </p>
    </div>
    @endif
</body>
</html>