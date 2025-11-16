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
        <h3 style="margin-top: 0; color: #007bff;">Resumen General</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
            <div class="text-center">
                <div style="font-size: 24px; font-weight: bold; color: #007bff;">{{ $summary['total_students'] ?? 0 }}</div>
                <div style="font-size: 12px; color: #6c757d;">Total Estudiantes</div>
            </div>
            <div class="text-center">
                <div style="font-size: 24px; font-weight: bold; color: #28a745;">{{ $summary['avg_attendance_rate'] ?? 0 }}%</div>
                <div style="font-size: 12px; color: #6c757d;">Asistencia Promedio</div>
            </div>
            <div class="text-center">
                <div style="font-size: 24px; font-weight: bold; color: #6c757d;">{{ $summary['total_sessions'] ?? 0 }}</div>
                <div style="font-size: 12px; color: #6c757d;">Total Sesiones</div>
            </div>
        </div>
    </div>
    @endif

    @if(!empty($student_data))
    <div class="section-title">
        <h3 style="margin: 0; font-size: 14px;">Datos por Estudiante ({{ count($student_data) }} registros)</h3>
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
            </tr>
        </thead>
        <tbody>
            @foreach($student_data as $student)
            <tr>
                <td>{{ $student['user_id'] }}</td>
                <td>{{ $student['student_name'] }}</td>
                <td>{{ $student['student_email'] }}</td>
                <td>{{ $student['group_name'] }}</td>
                <td>{{ $student['course_name'] }}</td>
                <td class="text-center">{{ $student['total_sessions'] }}</td>
                <td class="text-center bg-success">{{ $student['present_count'] }}</td>
                <td class="text-center bg-danger">{{ $student['absent_count'] }}</td>
                <td class="text-center bg-warning">{{ $student['late_count'] }}</td>
                <td class="text-center" style="font-weight: bold; 
                    @if($student['attendance_rate'] >= 80) color: #28a745;
                    @elseif($student['attendance_rate'] >= 60) color: #ffc107;
                    @else color: #dc3545;
                    @endif">
                    {{ $student['attendance_rate'] }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

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
            </tr>
        </thead>
        <tbody>
            @foreach($group_data as $group)
            <tr>
                <td>{{ $group['group_id'] }}</td>
                <td>{{ $group['group_name'] }}</td>
                <td>{{ $group['course_name'] }}</td>
                <td>{{ $group['course_version'] }}</td>
                <td class="text-center">{{ $group['total_students'] }}</td>
                <td class="text-center" style="font-weight: bold; 
                    @if($group['avg_attendance_rate'] >= 80) color: #28a745;
                    @elseif($group['avg_attendance_rate'] >= 60) color: #ffc107;
                    @else color: #dc3545;
                    @endif">
                    {{ $group['avg_attendance_rate'] }}%
                </td>
                <td class="text-center" style="color: #dc3545; font-weight: bold;">
                    {{ $group['avg_absence_rate'] }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(empty($student_data) && empty($group_data))
    <div style="text-align: center; padding: 40px; color: #6c757d;">
        <h3>No hay datos disponibles</h3>
        <p>No se encontraron registros de asistencia con los filtros aplicados.</p>
    </div>
    @endif
</body>
</html>