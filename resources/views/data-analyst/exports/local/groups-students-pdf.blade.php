{{-- resources/views/data-analyst/exports/local/groups-students-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .title { font-size: 16px; font-weight: bold; color: #2c3e50; }
        .subtitle { font-size: 12px; color: #7f8c8d; }
        .summary { margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 9px; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .table th { background-color: #FFF0F5; font-weight: bold; }
        .filters { margin-bottom: 15px; padding: 8px; background: #e9ecef; border-radius: 5px; font-size: 9px; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #95a5a6; border-top: 1px solid #ddd; padding-top: 8px; }
        .group-header { background-color: #f0f0f0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $title }}</div>
        <div class="subtitle">Generado el: {{ $export_date }}</div>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <strong>Filtros Aplicados:</strong><br>
        @foreach($filters as $key => $value)
            {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}<br>
        @endforeach
    </div>
    @endif

    <div class="summary">
        <strong>Resumen:</strong> 
        {{ $total_groups }} grupos encontrados con {{ $total_students }} estudiantes
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Grupo</th>
                <th>Curso</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Estado Grupo</th>
                <th>Estudiante</th>
                <th>Email</th>
                <th>Estado Académico</th>
                <th>Estado Pago</th>
                <th>Nota Final</th>
                <th>Asistencia %</th>
                <th>Fecha Matrícula</th>
            </tr>
        </thead>
        <tbody>
            @php
                $currentGroup = null;
            @endphp
            @foreach($groups as $group)
            <tr>
                <td>
                    @if($currentGroup != $group->group_id)
                        <strong>{{ $group->group_name }}</strong>
                        @php $currentGroup = $group->group_id; @endphp
                    @endif
                </td>
                <td>{{ $group->course_name }}</td>
                <td>{{ $group->start_date ? \Carbon\Carbon::parse($group->start_date)->format('d/m/Y') : '' }}</td>
                <td>{{ $group->end_date ? \Carbon\Carbon::parse($group->end_date)->format('d/m/Y') : '' }}</td>
                <td>{{ $group->group_status }}</td>
                <td>{{ $group->student_name }}</td>
                <td>{{ $group->student_email }}</td>
                <td>{{ $group->academic_status }}</td>
                <td>{{ $group->payment_status }}</td>
                <td>{{ $group->final_grade ?? 'N/A' }}</td>
                <td>{{ $group->attendance_percentage ?? 0 }}%</td>
                <td>{{ $group->enrollment_date ? \Carbon\Carbon::parse($group->enrollment_date)->format('d/m/Y') : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Reporte generado automáticamente por el Sistema DataAnalyst<br>
        {{ config('app.name') }} - {{ date('Y') }}
    </div>
</body>
</html>