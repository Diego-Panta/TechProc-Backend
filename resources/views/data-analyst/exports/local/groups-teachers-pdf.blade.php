{{-- resources/views/dataanalyst/exports/local/groups-teachers-pdf.blade.php --}}
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
        <strong>Resumen:</strong> {{ $total_groups }} grupos encontrados
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID Grupo</th>
                <th>Nombre Grupo</th>
                <th>Curso</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Estado</th>
                <th>Docente</th>
                <th>Email Docente</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groups as $group)
            <tr>
                <td>{{ $group->group_id }}</td>
                <td>{{ $group->group_name }}</td>
                <td>{{ $group->course_name }}</td>
                <td>{{ $group->start_date ? \Carbon\Carbon::parse($group->start_date)->format('d/m/Y') : '' }}</td>
                <td>{{ $group->end_date ? \Carbon\Carbon::parse($group->end_date)->format('d/m/Y') : '' }}</td>
                <td>{{ $group->group_status }}</td>
                <td>{{ $group->teacher_name ?? 'Sin asignar' }}</td>
                <td>{{ $group->teacher_email ?? 'N/A' }}</td>
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