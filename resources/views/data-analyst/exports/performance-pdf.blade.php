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
    </div>
    @endif

    @if(!empty($student_performance))
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
                    @if($student['final_grade'] >= 8) grade-high
                    @elseif($student['final_grade'] >= 6) grade-medium
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
                    @elseif($student['enrollment_status'] == 'pending')
                        <span class="status-pending">Pendiente</span>
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
                    @if($course['avg_final_grade'] >= 8) grade-high
                    @elseif($course['avg_final_grade'] >= 6) grade-medium
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

    @if(empty($student_performance) && empty($course_performance))
    <div style="text-align: center; padding: 40px; color: #6c757d;">
        <h3>No hay datos disponibles</h3>
        <p>No se encontraron registros de rendimiento con los filtros aplicados.</p>
    </div>
    @endif
</body>
</html>