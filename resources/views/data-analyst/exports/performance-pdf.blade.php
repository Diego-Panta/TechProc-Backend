{{-- resources/views/data-analyst/exports/performance-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .summary { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #dc3545; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .table th { background-color: #343a40; color: white; font-weight: bold; }
        .filters { margin-bottom: 20px; padding: 15px; background: #e9ecef; border-radius: 5px; }
        .section-title { background: #dc3545; color: white; padding: 8px; margin: 20px 0 10px 0; border-radius: 3px; }
        .page-break { page-break-after: always; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bg-success { background-color: #d4edda !important; }
        .bg-warning { background-color: #fff3cd !important; }
        .bg-danger { background-color: #f8d7da !important; }
        .grade-high { color: #28a745; font-weight: bold; }
        .grade-medium { color: #ffc107; font-weight: bold; }
        .grade-low { color: #dc3545; font-weight: bold; }
        .status-approved { background-color: #d4edda; color: #155724; padding: 2px 6px; border-radius: 3px; }
        .status-pending { background-color: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 3px; }
        .status-failed { background-color: #f8d7da; color: #721c24; padding: 2px 6px; border-radius: 3px; }
        .chart-section { margin-bottom: 25px; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; }
        .chart-title { background: #28a745; color: white; padding: 6px; margin: -15px -15px 10px -15px; border-radius: 3px 3px 0 0; }
        .mini-table { width: 100%; border-collapse: collapse; font-size: 9px; }
        .mini-table th, .mini-table td { border: 1px solid #ddd; padding: 4px; text-align: left; }
        .mini-table th { background-color: #6c757d; color: white; }
        .correlation-positive { background-color: #d4edda; }
        .correlation-negative { background-color: #f8d7da; }
        .correlation-neutral { background-color: #fff3cd; }
        .security-filters { background: #e8f5e8; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .data-scope { background: #e3f2fd; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; color: #343a40;">{{ $title }}</h1>
        <p style="margin: 5px 0; color: #6c757d;">Generado el: {{ $export_date }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <h3 style="margin-top: 0; color: #495057;">Filtros Aplicados en Datos Principales:</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
            @foreach($filters as $key => $value)
                <div>
                    <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> 
                    <span>{{ $value }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Información de filtros aplicados -->
    @if(!empty($filters_applied))
    <div class="filters">
        <h3 style="margin-top: 0; color: #495057;">Resumen de Filtros Aplicados:</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 8px;">
            @foreach($filters_applied as $filter)
                <div style="background: #ffffff; padding: 8px; border-radius: 3px; border-left: 3px solid #dc3545;">
                    {{ $filter }}
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Información del alcance de datos -->
    @if(!empty($data_scope))
    <div class="data-scope">
        <h3 style="margin-top: 0; color: #1976d2;">📊 Información del Alcance de Datos</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div>
                <strong>Filtros de fecha aplicados:</strong> 
                {{ $data_scope['date_filters_applied'] ? 'SÍ' : 'NO' }}
            </div>
            <div>
                <strong>Alcance:</strong> {{ $data_scope['scope'] ?? 'No especificado' }}
            </div>
        </div>
    </div>
    @endif

    <!-- Filtros de seguridad -->
    @if(!empty($summary['filters_applied']))
    <div class="security-filters">
        <h4 style="margin: 0 0 10px 0; color: #155724;">🔒 Filtros de Seguridad Aplicados</h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px;">
            <div>
                <strong>Estado del grupo:</strong> {{ $summary['filters_applied']['group_status'] ?? 'active' }}
            </div>
            <div>
                <strong>Estado académico:</strong> {{ $summary['filters_applied']['academic_status'] ?? 'active' }}
            </div>
            <div>
                <strong>Estado de pago:</strong> {{ $summary['filters_applied']['payment_status'] ?? 'paid' }}
            </div>
            <div>
                <strong>Tiene calificaciones:</strong> {{ $summary['filters_applied']['has_grades'] ? 'SÍ' : 'NO' }}
            </div>
        </div>
    </div>
    @endif

    @if(!empty($summary))
    <div class="summary">
        <h3 style="margin-top: 0; color: #dc3545;">Resumen General</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
            <div class="text-center">
                <div style="font-size: 24px; font-weight: bold; color: #007bff;">{{ $summary['total_students'] ?? 0 }}</div>
                <div style="font-size: 12px; color: #6c757d;">Total Estudiantes</div>
            </div>
            <div class="text-center">
                <div style="font-size: 24px; font-weight: bold; color: #6c757d;">{{ $summary['total_courses'] ?? 0 }}</div>
                <div style="font-size: 12px; color: #6c757d;">Total Cursos</div>
            </div>
            <div class="text-center">
                <div style="font-size: 24px; font-weight: bold; color: #28a745;">{{ $summary['overall_approval_rate'] ?? 0 }}%</div>
                <div style="font-size: 12px; color: #6c757d;">Aprobación General</div>
            </div>
            <div class="text-center">
                <div style="font-size: 24px; font-weight: bold; color: #dc3545;">{{ $summary['overall_avg_grade'] ?? 0 }}</div>
                <div style="font-size: 12px; color: #6c757d;">Calificación Promedio</div>
            </div>
        </div>
        <div style="margin-top: 15px; display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px;">
            <div class="text-center">
                <div style="font-size: 16px; font-weight: bold; color: #17a2b8;">{{ $summary['overall_avg_attendance'] ?? 0 }}%</div>
                <div style="font-size: 10px; color: #6c757d;">Asistencia Promedio</div>
            </div>
            <div class="text-center">
                <div style="font-size: 16px; font-weight: bold; 
                    @if($summary['data_consistency_check'] === 'verified') color: #28a745;
                    @else color: #dc3545;
                    @endif">
                    {{ $summary['data_consistency_check'] === 'verified' ? '✓' : '✗' }}
                </div>
                <div style="font-size: 10px; color: #6c757d;">Consistencia</div>
            </div>
        </div>
    </div>
    @endif

    <!-- SECCIÓN DE GRÁFICAS -->
    @if(!empty($charts))
    <div class="section-title">
        <h3 style="margin: 0; font-size: 14px;">Análisis Gráfico de Rendimiento</h3>
    </div>

    <!-- Distribución de Calificaciones -->
    @if(!empty($charts['grade_distribution']['grade_distribution']))
    <div class="chart-section">
        <div class="chart-title">
            <h4 style="margin: 0; font-size: 12px;">📊 Distribución de Calificaciones</h4>
        </div>
        <table class="mini-table">
            <thead>
                <tr>
                    <th>Rango</th>
                    <th>Estado</th>
                    <th>Cantidad Estudiantes</th>
                    <th>Proporción</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalStudents = max(1, $charts['grade_distribution']['statistics']['total_students'] ?? 1);
                @endphp
                @foreach($charts['grade_distribution']['grade_distribution'] as $distribution)
                <tr class="@if($distribution['status'] == 'Aprobado') correlation-positive @else correlation-negative @endif">
                    <td><strong>{{ $distribution['grade_range'] }}</strong></td>
                    <td>{{ $distribution['status'] }}</td>
                    <td class="text-center">{{ $distribution['student_count'] }}</td>
                    <td>
                        @php
                            $percentage = $totalStudents > 0 ? ($distribution['student_count'] / $totalStudents) * 100 : 0;
                            $percentage = min(100, max(0, $percentage)); // Limitar entre 0% y 100%
                        @endphp
                        <div style="background: #e9ecef; height: 15px; border-radius: 2px; position: relative; overflow: hidden;">
                            <div style="background: @if($distribution['status'] == 'Aprobado') #28a745 @else #dc3545 @endif; 
                                        height: 100%; width: {{ $percentage }}%; border-radius: 2px;"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if(!empty($charts['grade_distribution']['statistics']))
        @php $stats = $charts['grade_distribution']['statistics']; @endphp
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin-top: 10px;">
            <div style="text-align: center; background: #f8f9fa; padding: 8px; border-radius: 3px;">
                <div style="font-weight: bold; color: #007bff;">{{ $stats['total_students'] ?? 0 }}</div>
                <div style="font-size: 8px;">Total Estudiantes</div>
            </div>
            <div style="text-align: center; background: #f8f9fa; padding: 8px; border-radius: 3px;">
                <div style="font-weight: bold; color: #28a745;">{{ $stats['approval_rate'] ?? 0 }}%</div>
                <div style="font-size: 8px;">Tasa Aprobación</div>
            </div>
            <div style="text-align: center; background: #f8f9fa; padding: 8px; border-radius: 3px;">
                <div style="font-weight: bold; color: #6c757d;">{{ $stats['avg_grade'] ?? 0 }}</div>
                <div style="font-size: 8px;">Promedio General</div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Correlación Asistencia-Calificación -->
    @if(!empty($charts['attendance_grade_correlation']['scatter_data']))
    <div class="chart-section">
        <div class="chart-title">
            <h4 style="margin: 0; font-size: 12px;">📈 Correlación Asistencia vs Calificación</h4>
        </div>
        
        <!-- Resumen de Correlación -->
        @php
            $correlationData = $charts['attendance_grade_correlation'];
            $correlation = $correlationData['correlation'] ?? 0;
            $approvalStats = $correlationData['approval_stats'] ?? [];
            $summary = $correlationData['summary'] ?? [];
        @endphp
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin-bottom: 15px;">
            <div style="text-align: center; background: #e3f2fd; padding: 8px; border-radius: 3px;">
                <div style="font-weight: bold; color: #1976d2;">{{ number_format($correlation, 3) }}</div>
                <div style="font-size: 8px;">Coeficiente Correlación</div>
            </div>
            <div style="text-align: center; background: #e8f5e8; padding: 8px; border-radius: 3px;">
                <div style="font-weight: bold; color: #28a745;">{{ $approvalStats['approved'] ?? 0 }}</div>
                <div style="font-size: 8px;">Aprobados</div>
            </div>
            <div style="text-align: center; background: #ffebee; padding: 8px; border-radius: 3px;">
                <div style="font-weight: bold; color: #dc3545;">{{ $approvalStats['failed'] ?? 0 }}</div>
                <div style="font-size: 8px;">Reprobados</div>
            </div>
            <div style="text-align: center; background: #fff3e0; padding: 8px; border-radius: 3px;">
                <div style="font-weight: bold; color: #ff9800;">{{ $approvalStats['approval_rate'] ?? 0 }}%</div>
                <div style="font-size: 8px;">Tasa Aprobación</div>
            </div>
        </div>

        <table class="mini-table">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Grupo</th>
                    <th>Asistencia</th>
                    <th>Calificación</th>
                    <th>Estado</th>
                    <th>Exámenes</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($correlationData['scatter_data'], 0, 10) as $student)
                <tr class="@if($student['academic_status'] == 'Aprobado') correlation-positive @else correlation-negative @endif">
                    <td>{{ \Illuminate\Support\Str::limit($student['student_name'], 15) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($student['group_name'], 12) }}</td>
                    <td class="text-center">{{ $student['attendance_rate'] }}%</td>
                    <td class="text-center">{{ $student['avg_grade'] }}</td>
                    <td class="text-center">
                        @if($student['academic_status'] == 'Aprobado')
                            <span style="color: #28a745; font-weight: bold;">✓</span>
                        @else
                            <span style="color: #dc3545; font-weight: bold;">✗</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $student['total_exams'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if(count($correlationData['scatter_data']) > 10)
        <p style="margin: 5px 0; font-size: 9px; color: #6c757d;">
            <em>Mostrando 10 de {{ count($correlationData['scatter_data']) }} estudiantes</em>
        </p>
        @endif
    </div>
    @endif

    <!-- Rendimiento por Grupo -->
    @if(!empty($charts['group_performance_radar']['group_performance']))
    <div class="chart-section">
        <div class="chart-title">
            <h4 style="margin: 0; font-size: 12px;">🎯 Rendimiento por Grupo</h4>
        </div>
        <table class="mini-table">
            <thead>
                <tr>
                    <th>Grupo</th>
                    <th>Curso</th>
                    <th>Estudiantes</th>
                    <th>Calificación</th>
                    <th>Asistencia</th>
                    <th>Aprobación</th>
                    <th>Puntuación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($charts['group_performance_radar']['group_performance'] as $group)
                <tr>
                    <td><strong>{{ $group['group_name'] }}</strong></td>
                    <td>{{ \Illuminate\Support\Str::limit($group['course_name'], 15) }}</td>
                    <td class="text-center">{{ $group['total_students'] }}</td>
                    <td class="text-center" style="font-weight: bold;
                        @if($group['avg_final_grade'] >= 14) color: #28a745;
                        @elseif($group['avg_final_grade'] >= 11) color: #ffc107;
                        @else color: #dc3545;
                        @endif">
                        {{ $group['avg_final_grade'] }}
                    </td>
                    <td class="text-center">{{ $group['avg_attendance'] }}%</td>
                    <td class="text-center" style="font-weight: bold;
                        @if($group['approval_rate'] >= 80) color: #28a745;
                        @elseif($group['approval_rate'] >= 60) color: #ffc107;
                        @else color: #dc3545;
                        @endif">
                        {{ $group['approval_rate'] }}%
                    </td>
                    <td class="text-center" style="font-weight: bold;
                        @if($group['performance_score'] >= 80) color: #28a745;
                        @elseif($group['performance_score'] >= 60) color: #ffc107;
                        @else color: #dc3545;
                        @endif">
                        {{ $group['performance_score'] }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Filtros aplicados en gráficas -->
    @if(!empty($charts['grade_distribution']['filters_applied']))
    <div class="chart-section">
        <div class="chart-title">
            <h4 style="margin: 0; font-size: 12px;">⚙️ Filtros Aplicados en Gráficas</h4>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px;">
            @foreach($charts['grade_distribution']['filters_applied'] as $filter)
                <div style="background: #e3f2fd; padding: 6px; border-radius: 3px; font-size: 9px;">
                    {{ $filter }}
                </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif

    @if(!empty($student_performance))
    <div class="page-break"></div>
    
    <div class="section-title">
        <h3 style="margin: 0; font-size: 14px;">Rendimiento por Estudiante ({{ count($student_performance) }} estudiantes)</h3>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Estudiante</th>
                <th>Email</th>
                <th>Grupo</th>
                <th>Curso</th>
                <th>Calificación Final</th>
                <th>Asistencia</th>
                <th>Estado</th>
                <th>Total Exámenes</th>
                <th>Promedio General</th>
                <th>Mínima</th>
                <th>Máxima</th>
            </tr>
        </thead>
        <tbody>
            @foreach($student_performance as $student)
            <tr>
                <td>{{ $student['user_id'] }}</td>
                <td>{{ $student['student_name'] }}</td>
                <td>{{ $student['student_email'] }}</td>
                <td>{{ $student['group_name'] }}</td>
                <td>{{ $student['course_name'] }}</td>
                <td class="text-center 
                    @if($student['final_grade'] >= 14) grade-high
                    @elseif($student['final_grade'] >= 11) grade-medium
                    @else grade-low
                    @endif">
                    {{ $student['final_grade'] ?? 'N/A' }}
                </td>
                <td class="text-center 
                    @if($student['attendance_percentage'] >= 80) grade-high
                    @elseif($student['attendance_percentage'] >= 60) grade-medium
                    @else grade-low
                    @endif">
                    {{ $student['attendance_percentage'] ?? 0 }}%
                </td>
                <td class="text-center">
                    @if($student['enrollment_status'] == 'approved')
                        <span class="status-approved">Aprobado</span>
                    @elseif($student['enrollment_status'] == 'in_progress')
                        <span class="status-pending">En Progreso</span>
                    @else
                        <span class="status-failed">Reprobado</span>
                    @endif
                </td>
                <td class="text-center">{{ $student['total_exams_taken'] }}</td>
                <td class="text-center">{{ $student['overall_avg_grade'] ?? 'N/A' }}</td>
                <td class="text-center" style="color: #dc3545;">{{ $student['min_grade'] ?? 'N/A' }}</td>
                <td class="text-center" style="color: #28a745;">{{ $student['max_grade'] ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(!empty($course_performance))
    <div class="page-break"></div>
    
    <div class="section-title">
        <h3 style="margin: 0; font-size: 14px;">Rendimiento por Curso ({{ count($course_performance) }} cursos/grupos)</h3>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Grupo</th>
                <th>Curso</th>
                <th>Versión</th>
                <th>Total Estudiantes</th>
                <th>Calificación Final Promedio</th>
                <th>Asistencia Promedio</th>
                <th>Estudiantes Aprobados</th>
                <th>Tasa de Aprobación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($course_performance as $course)
            <tr>
                <td>{{ $course['group_name'] }}</td>
                <td>{{ $course['course_name'] }}</td>
                <td>{{ $course['course_version'] }}</td>
                <td class="text-center">{{ $course['total_students'] }}</td>
                <td class="text-center 
                    @if($course['avg_final_grade'] >= 14) grade-high
                    @elseif($course['avg_final_grade'] >= 11) grade-medium
                    @else grade-low
                    @endif">
                    {{ $course['avg_final_grade'] ?? 'N/A' }}
                </td>
                <td class="text-center 
                    @if($course['avg_attendance'] >= 80) grade-high
                    @elseif($course['avg_attendance'] >= 60) grade-medium
                    @else grade-low
                    @endif">
                    {{ $course['avg_attendance'] ?? 0 }}%
                </td>
                <td class="text-center bg-success">{{ $course['approved_students'] }}</td>
                <td class="text-center" style="font-weight: bold;
                    @if($course['approval_rate'] >= 80) color: #28a745;
                    @elseif($course['approval_rate'] >= 60) color: #ffc107;
                    @else color: #dc3545;
                    @endif">
                    {{ $course['approval_rate'] }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(empty($student_performance) && empty($course_performance) && empty($charts))
    <div style="text-align: center; padding: 40px; color: #6c757d;">
        <h3>No hay datos disponibles</h3>
        <p>No se encontraron registros de rendimiento con los filtros aplicados.</p>
    </div>
    @endif

    <!-- Resumen Ejecutivo -->
    @if(!empty($summary) && !empty($charts))
    <div class="page-break"></div>
    
    <div class="section-title">
        <h3 style="margin: 0; font-size: 14px;">Resumen Ejecutivo</h3>
    </div>

    <div class="summary">
        <h4 style="margin-top: 0; color: #495057;">📈 Hallazgos Principales</h4>
        
        @php
            $totalStudents = $summary['total_students'] ?? 0;
            $approvalRate = $summary['overall_approval_rate'] ?? 0;
            $avgGrade = $summary['overall_avg_grade'] ?? 0;
            $avgAttendance = $summary['overall_avg_attendance'] ?? 0;
            
            // Análisis de rendimiento
            $performanceLevel = '';
            if ($approvalRate >= 80) $performanceLevel = 'Excelente';
            elseif ($approvalRate >= 60) $performanceLevel = 'Bueno';
            elseif ($approvalRate >= 40) $performanceLevel = 'Regular';
            else $performanceLevel = 'Necesita Mejora';
            
            // Análisis de correlación
            $correlation = $charts['attendance_grade_correlation']['correlation'] ?? 0;
            $correlationStrength = '';
            if (abs($correlation) >= 0.7) $correlationStrength = 'Fuerte';
            elseif (abs($correlation) >= 0.3) $correlationStrength = 'Moderada';
            else $correlationStrength = 'Débil';
            
            $correlationDirection = $correlation > 0 ? 'positiva' : 'negativa';
        @endphp

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
            <div style="background: #e8f5e8; padding: 15px; border-radius: 5px; text-align: center;">
                <div style="font-size: 16px; font-weight: bold; color: #28a745;">{{ $performanceLevel }}</div>
                <div style="font-size: 10px; color: #6c757d;">Nivel de Rendimiento</div>
            </div>
            <div style="background: #e3f2fd; padding: 15px; border-radius: 5px; text-align: center;">
                <div style="font-size: 16px; font-weight: bold; color: #1976d2;">{{ $correlationStrength }}</div>
                <div style="font-size: 10px; color: #6c757d;">Correlación {{ $correlationDirection }}</div>
            </div>
            <div style="background: #fff3e0; padding: 15px; border-radius: 5px; text-align: center;">
                <div style="font-size: 16px; font-weight: bold; color: #ff9800;">{{ $totalStudents }}</div>
                <div style="font-size: 10px; color: #6c757d;">Estudiantes Analizados</div>
            </div>
        </div>

        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
            <h5 style="margin: 0 0 10px 0; color: #495057;">📋 Recomendaciones</h5>
            <ul style="margin: 0; padding-left: 20px; font-size: 9px;">
                @if($approvalRate < 60)
                <li>Implementar estrategias de refuerzo académico para mejorar tasas de aprobación</li>
                @endif
                @if($avgAttendance < 80)
                <li>Fortalecer programas de retención y seguimiento de asistencia</li>
                @endif
                @if($correlation > 0.5)
                <li>Capitalizar la relación positiva entre asistencia y rendimiento académico</li>
                @endif
                @if(!empty($course_performance) && count($course_performance) > 1)
                <li>Replicar mejores prácticas de los grupos con mejor rendimiento</li>
                @endif
            </ul>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">
            <div style="text-align: center;">
                <div style="font-size: 12px; font-weight: bold; color: #28a745;">{{ $approvalRate }}%</div>
                <div style="font-size: 8px; color: #6c757d;">Meta Aprobación</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 12px; font-weight: bold; color: #17a2b8;">{{ $avgAttendance }}%</div>
                <div style="font-size: 8px; color: #6c757d;">Meta Asistencia</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 12px; font-weight: bold; color: #ffc107;">{{ $avgGrade }}/20</div>
                <div style="font-size: 8px; color: #6c757d;">Meta Calificación</div>
            </div>
        </div>
    </div>
    @endif
</body>
</html>