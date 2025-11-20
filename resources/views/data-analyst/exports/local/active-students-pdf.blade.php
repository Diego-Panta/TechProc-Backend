{{-- resources/views/data-analyst/exports/local/active-students-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; color: #2c3e50; }
        .subtitle { font-size: 14px; color: #7f8c8d; }
        .summary { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        .filters { margin-bottom: 20px; padding: 10px; background: #e9ecef; border-radius: 5px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #95a5a6; border-top: 1px solid #ddd; padding-top: 10px; }
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
        <strong>Resumen:</strong> {{ $total_students }} estudiantes activos encontrados
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Grupo</th>
                <th>Curso</th>
                <th>Estado Académico</th>
                <th>Estado Pago</th>
                <th>Nota Final</th>
                <th>Asistencia %</th>
                <th>Fecha Matrícula</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            <tr>
                <td>{{ $student->student_id }}</td>
                <td>{{ $student->student_name }}</td>
                <td>{{ $student->student_email }}</td>
                <td>{{ $student->group_name }}</td>
                <td>{{ $student->course_name }}</td>
                <td>{{ $student->academic_status }}</td>
                <td>{{ $student->payment_status }}</td>
                <td>{{ $student->final_grade ?? 'N/A' }}</td>
                <td>{{ $student->attendance_percentage ?? 0 }}%</td>
                <td>{{ $student->enrollment_date ? \Carbon\Carbon::parse($student->enrollment_date)->format('d/m/Y') : '' }}</td>
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