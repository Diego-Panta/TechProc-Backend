{{-- resources/views/data-analyst/exports/local/grades-summary-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .title { font-size: 16px; font-weight: bold; color: #2c3e50; }
        .subtitle { font-size: 12px; color: #7f8c8d; }
        .summary { margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table th, .table td { border: 1px solid #ddd; padding: 7px; text-align: left; }
        .table th { background-color: #F5F5DC; font-weight: bold; }
        .filters { margin-bottom: 15px; padding: 8px; background: #e9ecef; border-radius: 5px; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #95a5a6; border-top: 1px solid #ddd; padding-top: 8px; }
        .grade-excellent { background-color: #d4edda; }
        .grade-good { background-color: #fff3cd; }
        .grade-poor { background-color: #f8d7da; }
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
        <strong>Resumen:</strong> {{ $total_records }} calificaciones registradas
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Grupo</th>
                <th>Estudiante</th>
                <th>Módulo</th>
                <th>Examen</th>
                <th>Calificación</th>
                <th>Fecha Examen</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grades as $grade)
            <tr>
                <td>{{ $grade->group_name }}</td>
                <td>{{ $grade->student_name }}</td>
                <td>{{ $grade->module_title }}</td>
                <td>{{ $grade->exam_title }}</td>
                <td class="@if($grade->grade >= 90) grade-excellent @elseif($grade->grade >= 70) grade-good @else grade-poor @endif">
                    {{ $grade->grade ?? 'N/A' }}
                </td>
                <td>{{ $grade->exam_date ? \Carbon\Carbon::parse($grade->exam_date)->format('d/m/Y H:i') : '' }}</td>
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