{{-- resources/views/data-analyst/exports/local/default-pdf.blade.php --}}
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
        .info-section { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        .filters { margin-bottom: 20px; padding: 15px; background: #e9ecef; border-radius: 5px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #95a5a6; border-top: 1px solid #ddd; padding-top: 10px; }
        .message-box { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $title }}</div>
        <div class="subtitle">Generado el: {{ $export_date }}</div>
    </div>

    <div class="info-section">
        <strong>Información del Reporte:</strong><br>
        <p>Título: {{ $title }}</p>
        <p>Fecha de Exportación: {{ $export_date }}</p>
        <p>Total de Registros: {{ $total_records ?? 'No especificado' }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <strong>Filtros Aplicados:</strong><br>
        @foreach($filters as $key => $value)
            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}<br>
        @endforeach
    </div>
    @endif

    <div class="message-box">
        <strong>Nota:</strong><br>
        No se encontró una plantilla específica para este tipo de reporte.<br>
        Por favor, contacte al administrador del sistema para configurar la exportación adecuada.
    </div>

    <div class="footer">
        Reporte generado automáticamente por el Sistema DataAnalyst<br>
        {{ config('app.name') }} - {{ date('Y') }}
    </div>
</body>
</html>