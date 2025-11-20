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
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; color: #343a40;">{{ $title }}</h1>
        <p style="margin: 5px 0; color: #6c757d;">Generado el: {{ $export_date }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <h3 style="margin-top: 0; color: #495057;">Filtros Aplicados:</h3>
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
                <div style="font-size: 24px; font-weight: bold; color: #6c757d;">{{ count($module_data ?? []) }}</div>
                <div style="font-size: 12px; color: #6c757d;">Módulos Registrados</div>
            </div>
            <div class="text-center">
                <div style="font-size: 24px; font-weight: bold; color: #6c757d;">{{ count($grade_data ?? []) }}</div>
                <div style="font-size: 12px; color: #6c757d;">Registros Calificaciones</div>
            </div>
        </div>
    </div>
    @endif

    @if(!empty($module_data))
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
                    @if($grade['avg_grade'] >= 8) color: #28a745;
                    @elseif($grade['avg_grade'] >= 6) color: #ffc107;
                    @else color: #dc3545;
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

    @if(empty($module_data) && empty($grade_data))
    <div style="text-align: center; padding: 40px; color: #6c757d;">
        <h3>No hay datos disponibles</h3>
        <p>No se encontraron registros de progreso con los filtros aplicados.</p>
    </div>
    @endif
</body>
</html>