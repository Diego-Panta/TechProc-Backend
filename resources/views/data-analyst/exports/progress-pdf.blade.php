{{-- resources/views/data-analyst/exports/progress-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .summary { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #28a745; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .table th { background-color: #343a40; color: white; font-weight: bold; }
        .filters { margin-bottom: 20px; padding: 15px; background: #e9ecef; border-radius: 5px; }
        .section-title { background: #28a745; color: white; padding: 8px; margin: 20px 0 10px 0; border-radius: 3px; }
        .page-break { page-break-after: always; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bg-success { background-color: #d4edda !important; }
        .bg-warning { background-color: #fff3cd !important; }
        .bg-danger { background-color: #f8d7da !important; }
        .completion-high { color: #28a745; font-weight: bold; }
        .completion-medium { color: #ffc107; font-weight: bold; }
        .completion-low { color: #dc3545; font-weight: bold; }
        .chart-section { margin-bottom: 25px; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; }
        .chart-title { background: #17a2b8; color: white; padding: 6px; margin: -15px -15px 10px -15px; border-radius: 3px 3px 0 0; }
        .mini-table { width: 100%; border-collapse: collapse; font-size: 9px; }
        .mini-table th, .mini-table td { border: 1px solid #ddd; padding: 4px; text-align: left; }
        .mini-table th { background-color: #6c757d; color: white; }
        .evolution-improving { background-color: #d4edda; }
        .evolution-declining { background-color: #f8d7da; }
        .evolution-stable { background-color: #fff3cd; }
        .student-section { margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 3px; border-left: 4px solid #007bff; }
        .grade-high { color: #28a745; font-weight: bold; }
        .grade-medium { color: #ffc107; font-weight: bold; }
        .grade-low { color: #dc3545; font-weight: bold; }
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
                <div style="background: #ffffff; padding: 8px; border-radius: 3px; border-left: 3px solid #28a745;">
                    {{ $filter }}
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Información del rango de datos -->
    @if(!empty($data_range_info))
    <div class="summary">
        <h3 style="margin-top: 0; color: #17a2b8;">Información del Rango de Datos</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div>
                <strong>Filtros de fecha aplicados:</strong> 
                {{ $data_range_info['date_filters_applied'] ? 'SÍ' : 'NO' }}
            </div>
            <div>
                <strong>Alcance:</strong> {{ $data_range_info['scope'] ?? 'No especificado' }}
            </div>
            <div>
                <strong>Nota:</strong> Los filtros aplican a sesiones de clase y fechas de examen
            </div>
        </div>
    </div>
    @endif

    @if(!empty($summary))
    <div class="summary">
        <h3 style="margin-top: 0; color: #28a745;">Resumen General</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
            <div class="text-center">
                <div style="font-size: 24px; font-weight: bold; color: #28a745;">{{ $summary['avg_completion_rate'] ?? 0 }}%</div>
                <div style="font-size: 12px; color: #6c757d;">Completación Promedio</div>
            </div>
            <div class="text-center">
                <div style="font-size: 24px; font-weight: bold; color: #007bff;">{{ $summary['avg_grade'] ?? 0 }}</div>
                <div style="font-size: 12px; color: #6c757d;">Calificación Promedio</div>
            </div>
            <div class="text-center">
                <div style="font-size: 24px; font-weight: bold; color: #6c757d;">{{ $summary['total_students'] ?? 0 }}</div>
                <div style="font-size: 12px; color: #6c757d;">Estudiantes Únicos</div>
            </div>
            <div class="text-center">
                <div style="font-size: 24px; font-weight: bold; color: #6c757d;">{{ $summary['total_modules'] ?? 0 }}</div>
                <div style="font-size: 12px; color: #6c757d;">Módulos Registrados</div>
            </div>
        </div>
        <div style="margin-top: 15px; display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px;">
            <div class="text-center">
                <div style="font-size: 16px; font-weight: bold; color: #17a2b8;">{{ $summary['total_grades'] ?? 0 }}</div>
                <div style="font-size: 10px; color: #6c757d;">Calificaciones</div>
            </div>
            <div class="text-center">
                <div style="font-size: 16px; font-weight: bold; color: #28a745;">{{ count($module_data ?? []) }}</div>
                <div style="font-size: 10px; color: #6c757d;">Registros Módulos</div>
            </div>
            <div class="text-center">
                <div style="font-size: 16px; font-weight: bold; color: #007bff;">{{ count($grade_data ?? []) }}</div>
                <div style="font-size: 10px; color: #6c757d;">Registros Calificaciones</div>
            </div>
        </div>
    </div>
    @endif

    <!-- SECCIÓN DE GRÁFICAS -->
    @if(!empty($charts))
    <div class="section-title">
        <h3 style="margin: 0; font-size: 14px;">Análisis de Evolución del Progreso</h3>
    </div>

    <!-- Evolución de Calificaciones -->
    @if(!empty($charts['grade_evolution']['grade_evolution']))
    <div class="chart-section">
        <div class="chart-title">
            <h4 style="margin: 0; font-size: 12px;">📈 Evolución de Calificaciones</h4>
        </div>
        
        @php
            $gradeEvolution = $charts['grade_evolution']['grade_evolution'];
            // Agrupar por estudiante
            $students = [];
            foreach ($gradeEvolution as $record) {
                $studentName = $record['student_name'] ?? '';
                if (!isset($students[$studentName])) {
                    $students[$studentName] = [
                        'group' => $record['group_name'] ?? '',
                        'records' => []
                    ];
                }
                $students[$studentName]['records'][] = $record;
            }
            
            // Calcular estadísticas generales
            $totalRecords = count($gradeEvolution);
            $uniqueStudents = count($students);
            $examsPerStudent = $totalRecords / max(1, $uniqueStudents);
            
            // Calcular tendencia general
            $firstGrades = [];
            $lastGrades = [];
            foreach ($students as $studentData) {
                if (count($studentData['records']) > 1) {
                    $firstGrades[] = $studentData['records'][0]['grade'] ?? 0;
                    $lastGrades[] = end($studentData['records'])['grade'] ?? 0;
                }
            }
            
            $avgImprovement = 0;
            $generalTrend = 'Estable';
            if (!empty($firstGrades) && !empty($lastGrades)) {
                $avgFirst = array_sum($firstGrades) / count($firstGrades);
                $avgLast = array_sum($lastGrades) / count($lastGrades);
                $avgImprovement = $avgLast - $avgFirst;
                $generalTrend = $avgImprovement > 0 ? 'Mejorando' : ($avgImprovement < 0 ? 'Decreciendo' : 'Estable');
            }
        @endphp

        <!-- Resumen Estadístico -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin-bottom: 15px;">
            <div style="text-align: center; background: #e3f2fd; padding: 8px; border-radius: 3px;">
                <div style="font-weight: bold; color: #1976d2;">{{ $totalRecords }}</div>
                <div style="font-size: 8px;">Total Exámenes</div>
            </div>
            <div style="text-align: center; background: #e8f5e8; padding: 8px; border-radius: 3px;">
                <div style="font-weight: bold; color: #28a745;">{{ $uniqueStudents }}</div>
                <div style="font-size: 8px;">Estudiantes</div>
            </div>
            <div style="text-align: center; background: #fff3e0; padding: 8px; border-radius: 3px;">
                <div style="font-weight: bold; color: #ff9800;">{{ number_format($examsPerStudent, 1) }}</div>
                <div style="font-size: 8px;">Promedio Exámenes</div>
            </div>
            <div style="text-align: center; background: 
                @if($avgImprovement > 0) #e8f5e8
                @elseif($avgImprovement < 0) #ffebee
                @else #fff3e0
                @endif; padding: 8px; border-radius: 3px;">
                <div style="font-weight: bold; 
                    @if($avgImprovement > 0) color: #28a745;
                    @elseif($avgImprovement < 0) color: #dc3545;
                    @else color: #ff9800;
                    @endif">
                    {{ number_format($avgImprovement, 2) }}
                </div>
                <div style="font-size: 8px;">Mejora Promedio</div>
            </div>
        </div>

        <!-- Evolución por Estudiantes (mostrar máximo 3) -->
        @php $studentCount = 0; @endphp
        @foreach($students as $studentName => $studentData)
            @if($studentCount < 3) <!-- Mostrar solo 3 estudiantes para no saturar el PDF -->
            <div class="student-section">
                <h5 style="margin: 0 0 8px 0; color: #007bff;">
                    👤 {{ $studentName }} - Grupo: {{ $studentData['group'] }}
                </h5>
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Examen</th>
                            <th>Módulo</th>
                            <th>Calificación</th>
                            <th>Tendencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $previousGrade = null; @endphp
                        @foreach(array_slice($studentData['records'], 0, 6) as $record) <!-- Máximo 6 exámenes -->
                        <tr class="@if($record['grade'] >= 11) evolution-improving @else evolution-declining @endif">
                            <td>{{ $record['exam_date'] ?? 'N/A' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($record['exam_title'] ?? '', 20) }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($record['module_title'] ?? '', 15) }}</td>
                            <td class="text-center" style="font-weight: bold;
                                @if($record['grade'] >= 14) color: #28a745;
                                @elseif($record['grade'] >= 11) color: #ffc107;
                                @else color: #dc3545;
                                @endif">
                                {{ $record['grade'] }}
                            </td>
                            <td class="text-center">
                                @if($previousGrade !== null)
                                    @if($record['grade'] > $previousGrade)
                                        <span style="color: #28a745;">📈</span>
                                    @elseif($record['grade'] < $previousGrade)
                                        <span style="color: #dc3545;">📉</span>
                                    @else
                                        <span style="color: #ffc107;">➡️</span>
                                    @endif
                                @else
                                    <span style="color: #6c757d;">🆕</span>
                                @endif
                            </td>
                        </tr>
                        @php $previousGrade = $record['grade']; @endphp
                        @endforeach
                    </tbody>
                </table>
                @if(count($studentData['records']) > 6)
                <p style="margin: 5px 0; font-size: 8px; color: #6c757d;">
                    <em>Mostrando 6 de {{ count($studentData['records']) }} exámenes</em>
                </p>
                @endif
            </div>
            @php $studentCount++; @endphp
            @endif
        @endforeach

        @if(count($students) > 3)
        <p style="margin: 10px 0; font-size: 9px; color: #6c757d; text-align: center;">
            <em>Mostrando 3 de {{ count($students) }} estudiantes. Ver archivo Excel para todos los datos.</em>
        </p>
        @endif

        <!-- Filtros aplicados en gráficas -->
        @if(!empty($charts['grade_evolution']['filters_applied']))
        <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 3px;">
            <h5 style="margin: 0 0 5px 0; color: #6c757d; font-size: 10px;">⚙️ Filtros Aplicados en Gráficas:</h5>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 5px;">
                @foreach($charts['grade_evolution']['filters_applied'] as $filter)
                    <div style="background: #ffffff; padding: 4px; border-radius: 2px; font-size: 8px; border-left: 2px solid #17a2b8;">
                        {{ $filter }}
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif
    @endif

    <!-- SECCIÓN DE COMPLETACIÓN DE MÓDULOS -->
    @if(!empty($module_data))
    <div class="page-break"></div>
    
    <div class="section-title">
        <h3 style="margin: 0; font-size: 14px;">Completación de Módulos ({{ count($module_data) }} registros)</h3>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>ID Estudiante</th>
                <th>Estudiante</th>
                <th>Email</th>
                <th>Grupo</th>
                <th>Curso</th>
                <th>Módulo</th>
                <th>Orden</th>
                <th>Total Sesiones</th>
                <th>Sesiones Atendidas</th>
                <th>Tasa Completación</th>
                <th>Días Completar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($module_data as $module)
            <tr>
                <td>{{ $module['user_id'] }}</td>
                <td>{{ $module['student_name'] }}</td>
                <td>{{ $module['student_email'] }}</td>
                <td>{{ $module['group_name'] }}</td>
                <td>{{ $module['course_name'] }}</td>
                <td>{{ $module['module_title'] }}</td>
                <td class="text-center">{{ $module['module_order'] }}</td>
                <td class="text-center">{{ $module['total_sessions'] }}</td>
                <td class="text-center bg-success">{{ $module['attended_sessions'] }}</td>
                <td class="text-center 
                    @if($module['completion_rate'] >= 80) completion-high
                    @elseif($module['completion_rate'] >= 60) completion-medium
                    @else completion-low
                    @endif">
                    {{ $module['completion_rate'] }}%
                </td>
                <td class="text-center">{{ $module['completion_days'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- SECCIÓN DE CONSISTENCIA DE CALIFICACIONES -->
    @if(!empty($grade_data))
    <div class="page-break"></div>
    
    <div class="section-title">
        <h3 style="margin: 0; font-size: 14px;">Consistencia de Calificaciones ({{ count($grade_data) }} registros)</h3>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>ID Estudiante</th>
                <th>Estudiante</th>
                <th>Email</th>
                <th>Grupo</th>
                <th>Curso</th>
                <th>Total Calificaciones</th>
                <th>Promedio</th>
                <th>Desviación Estándar</th>
                <th>Mínima</th>
                <th>Máxima</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grade_data as $grade)
            <tr>
                <td>{{ $grade['user_id'] }}</td>
                <td>{{ $grade['student_name'] }}</td>
                <td>{{ $grade['student_email'] }}</td>
                <td>{{ $grade['group_name'] }}</td>
                <td>{{ $grade['course_name'] }}</td>
                <td class="text-center">{{ $grade['total_grades'] }}</td>
                <td class="text-center" style="font-weight: bold;
                    @if($grade['avg_grade'] >= 8) grade-high
                    @elseif($grade['avg_grade'] >= 6) grade-medium
                    @else grade-low
                    @endif">
                    {{ $grade['avg_grade'] }}
                </td>
                <td class="text-center">{{ $grade['grade_stddev'] }}</td>
                <td class="text-center" style="color: #dc3545;">{{ $grade['min_grade'] }}</td>
                <td class="text-center" style="color: #28a745;">{{ $grade['max_grade'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- RESUMEN FINAL -->
    @if(!empty($summary) && (empty($module_data) || empty($grade_data)))
    <div class="page-break"></div>
    
    <div class="section-title">
        <h3 style="margin: 0; font-size: 14px;">Resumen Ejecutivo</h3>
    </div>
    
    <div class="summary">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div style="text-align: center;">
                <h4 style="color: #28a745; margin-bottom: 10px;">📊 Completación</h4>
                <div style="font-size: 32px; font-weight: bold; color: #28a745;">{{ $summary['avg_completion_rate'] ?? 0 }}%</div>
                <p style="font-size: 10px; color: #6c757d; margin: 5px 0;">
                    {{ $summary['total_modules'] ?? 0 }} módulos<br>
                    {{ count($module_data ?? []) }} registros
                </p>
            </div>
            
            <div style="text-align: center;">
                <h4 style="color: #007bff; margin-bottom: 10px;">🎓 Rendimiento</h4>
                <div style="font-size: 32px; font-weight: bold; color: #007bff;">{{ $summary['avg_grade'] ?? 0 }}</div>
                <p style="font-size: 10px; color: #6c757d; margin: 5px 0;">
                    {{ $summary['total_grades'] ?? 0 }} calificaciones<br>
                    {{ count($grade_data ?? []) }} registros
                </p>
            </div>
            
            <div style="text-align: center;">
                <h4 style="color: #6c757d; margin-bottom: 10px;">👥 Estudiantes</h4>
                <div style="font-size: 32px; font-weight: bold; color: #6c757d;">{{ $summary['total_students'] ?? 0 }}</div>
                <p style="font-size: 10px; color: #6c757d; margin: 5px 0;">
                    Únicos en el sistema<br>
                    Con datos de progreso
                </p>
            </div>
        </div>
        
        <!-- Indicadores de calidad -->
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
            <h4 style="color: #495057; margin-bottom: 10px;">📈 Indicadores de Calidad</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">
                <div style="text-align: center;">
                    <div style="font-size: 14px; font-weight: bold; 
                        @if(($summary['avg_completion_rate'] ?? 0) >= 80) color: #28a745;
                        @elseif(($summary['avg_completion_rate'] ?? 0) >= 60) color: #ffc107;
                        @else color: #dc3545;
                        @endif">
                        @if(($summary['avg_completion_rate'] ?? 0) >= 80) ✅ Excelente
                        @elseif(($summary['avg_completion_rate'] ?? 0) >= 60) ⚡ Bueno
                        @else ❌ Necesita mejora
                        @endif
                    </div>
                    <div style="font-size: 9px; color: #6c757d;">Completación</div>
                </div>
                
                <div style="text-align: center;">
                    <div style="font-size: 14px; font-weight: bold;
                        @if(($summary['avg_grade'] ?? 0) >= 14) color: #28a745;
                        @elseif(($summary['avg_grade'] ?? 0) >= 11) color: #ffc107;
                        @else color: #dc3545;
                        @endif">
                        @if(($summary['avg_grade'] ?? 0) >= 14) ✅ Sobresaliente
                        @elseif(($summary['avg_grade'] ?? 0) >= 11) ⚡ Aprobado
                        @else ❌ Reprobado
                        @endif
                    </div>
                    <div style="font-size: 9px; color: #6c757d;">Rendimiento</div>
                </div>
                
                <div style="text-align: center;">
                    <div style="font-size: 14px; font-weight: bold; color: #17a2b8;">
                        @if(($summary['total_students'] ?? 0) > 0) 👥 Activos
                        @else 👥 Sin datos
                        @endif
                    </div>
                    <div style="font-size: 9px; color: #6c757d;">Participación</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(empty($module_data) && empty($grade_data) && empty($charts))
    <div style="text-align: center; padding: 40px; color: #6c757d;">
        <h3>No hay datos disponibles</h3>
        <p>No se encontraron registros de progreso con los filtros aplicados.</p>
        @if(!empty($filters))
        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 5px; display: inline-block;">
            <h4 style="margin: 0; color: #856404;">Sugerencias:</h4>
            <ul style="text-align: left; margin: 10px 0 0 0; padding-left: 20px;">
                <li>Verifique que los filtros de fecha sean correctos</li>
                <li>Confirme que el grupo seleccionado tenga estudiantes activos</li>
                <li>Revise que existan sesiones de clase en el período seleccionado</li>
                <li>Verifique que haya exámenes registrados en el período</li>
            </ul>
        </div>
        @endif
    </div>
    @endif

    <!-- PIE DE PÁGINA -->
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; color: #6c757d; font-size: 8px;">
        <p>Reporte generado automáticamente por el Sistema de Analytics - {{ $export_date }}</p>
        <p>
            @if(!empty($data_range_info) && $data_range_info['date_filters_applied'])
                Período de datos: {{ $filters['start_date'] ?? 'Inicio' }} a {{ $filters['end_date'] ?? 'Actual' }}
            @else
                Datos acumulados - Todos los registros disponibles
            @endif
        </p>
        <p>Para consultas técnicas contactar al administrador del sistema</p>
    </div>
</body>
</html>