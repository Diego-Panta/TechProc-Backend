{{-- resources/views/data-analyst/exports/local/support-tickets-pdf.blade.php --}}
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
        .table th { background-color: #E6F3FF; font-weight: bold; }
        .filters { margin-bottom: 15px; padding: 8px; background: #e9ecef; border-radius: 5px; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #95a5a6; border-top: 1px solid #ddd; padding-top: 8px; }
        .priority-high { background-color: #f8d7da; color: #721c24; }
        .priority-medium { background-color: #fff3cd; color: #856404; }
        .priority-low { background-color: #d1ecf1; color: #0c5460; }
        .status-open { background-color: #d4edda; color: #155724; }
        .status-closed { background-color: #f8f9fa; color: #6c757d; }
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
        <strong>Resumen:</strong> {{ $total_records }} tickets encontrados
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID Ticket</th>
                <th>Título</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Prioridad</th>
                <th>Usuarios</th>
                <th>Fecha Creación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
            <tr>
                <td>#{{ $ticket->ticket_id }}</td>
                <td>{{ $ticket->ticket_title }}</td>
                <td>{{ $ticket->ticket_type }}</td>
                <td class="status-{{ $ticket->ticket_status }}">
                    {{ ucfirst($ticket->ticket_status) }}
                </td>
                <td class="priority-{{ $ticket->ticket_priority }}">
                    {{ ucfirst($ticket->ticket_priority) }}
                </td>
                <td>{{ $ticket->student_name }}</td>
                <td>{{ $ticket->created_at ? \Carbon\Carbon::parse($ticket->created_at)->format('d/m/Y H:i') : '' }}</td>
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