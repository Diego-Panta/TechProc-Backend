{{-- resources/views/data-analyst/exports/local/quick-dashboard-pdf.blade.php --}}
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
        .metrics-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 25px; }
        .metric-card { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; text-align: center; }
        .metric-value { font-size: 24px; font-weight: bold; color: #2c3e50; margin: 10px 0; }
        .metric-label { font-size: 12px; color: #6c757d; text-transform: uppercase; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #95a5a6; border-top: 1px solid #ddd; padding-top: 10px; }
        .export-info { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $title }}</div>
        <div class="subtitle">Generado el: {{ $export_date }}</div>
    </div>

    <div class="export-info">
        <strong>Información del Reporte:</strong><br>
        Dashboard de métricas rápidas del sistema - Vista consolidada
    </div>

    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-label">Estudiantes Activos</div>
            <div class="metric-value">{{ $dashboard['total_students'] ?? 0 }}</div>
        </div>
        
        <div class="metric-card">
            <div class="metric-label">Grupos Activos</div>
            <div class="metric-value">{{ $dashboard['total_groups'] ?? 0 }}</div>
        </div>
        
        <div class="metric-card">
            <div class="metric-label">Pagos Pendientes</div>
            <div class="metric-value">{{ $dashboard['pending_payments'] ?? 0 }}</div>
        </div>
        
        <div class="metric-card">
            <div class="metric-label">Tickets Abiertos</div>
            <div class="metric-value">{{ $dashboard['open_tickets'] ?? 0 }}</div>
        </div>
        
        <div class="metric-card">
            <div class="metric-label">Sesiones Hoy</div>
            <div class="metric-value">{{ $dashboard['today_sessions'] ?? 0 }}</div>
        </div>
        
        <div class="metric-card">
            <div class="metric-label">Exámenes Próximos</div>
            <div class="metric-value">{{ $dashboard['upcoming_exams'] ?? 0 }}</div>
        </div>
    </div>

    <div class="footer">
        Reporte generado automáticamente por el Sistema DataAnalyst<br>
        {{ config('app.name') }} - {{ date('Y') }}
    </div>
</body>
</html>