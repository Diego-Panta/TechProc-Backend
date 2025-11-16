{{-- resources/views/data-analyst/exports/dropout-predictions-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .summary { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        .filters { margin-bottom: 20px; padding: 10px; background: #e9ecef; }
        .risk-high { background: #ff6b6b; color: white; font-weight: bold; }
        .risk-medium { background: #ffd93d; font-weight: bold; }
        .risk-low { background: #6bcf7f; color: white; font-weight: bold; }
        .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px; }
        .summary-item { padding: 15px; border-radius: 5px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Generado el: {{ $export_date }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <h3>Filtros Aplicados:</h3>
        <ul>
            @foreach($filters as $key => $value)
                <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(!empty($summary))
    <div class="summary">
        <h3>Resumen General</h3>
        
        <div class="summary-grid">
            <div class="summary-item" style="background: #ff6b6b; color: white;">
                <h4>Alto Riesgo</h4>
                <p style="font-size: 24px; margin: 5px 0;">{{ $summary['high_risk_count'] ?? 0 }}</p>
            </div>
            <div class="summary-item" style="background: #ffd93d;">
                <h4>Riesgo Medio</h4>
                <p style="font-size: 24px; margin: 5px 0;">{{ $summary['medium_risk_count'] ?? 0 }}</p>
            </div>
            <div class="summary-item" style="background: #6bcf7f; color: white;">
                <h4>Bajo Riesgo</h4>
                <p style="font-size: 24px; margin: 5px 0;">{{ $summary['low_risk_count'] ?? 0 }}</p>
            </div>
        </div>

        <p><strong>Total de estudiantes:</strong> {{ $summary['total_students'] ?? 0 }}</p>
        <p><strong>Probabilidad promedio de deserción:</strong> {{ round(($summary['avg_dropout_probability'] ?? 0) * 100, 1) }}%</p>
        
        @if(!empty($summary['data_status_summary']))
        <p><strong>Datos completos:</strong> {{ $summary['data_status_summary']['complete_data'] ?? 0 }} estudiantes</p>
        <p><strong>Faltan datos académicos:</strong> {{ $summary['data_status_summary']['missing_academic'] ?? 0 }} estudiantes</p>
        <p><strong>Faltan datos de asistencia:</strong> {{ $summary['data_status_summary']['missing_attendance'] ?? 0 }} estudiantes</p>
        @endif
    </div>
    @endif

    @if(!empty($predictions))
    <h3>Predicciones de Deserción</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Estudiante</th>
                <th>Grupo</th>
                <th>Probabilidad</th>
                <th>Nivel Riesgo</th>
                <th>Acción Recomendada</th>
                <th>Nota Prom.</th>
                <th>Asistencia</th>
                <th>Pagos</th>
                <th>Estado Datos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($predictions as $prediction)
            <tr>
                <td>{{ $prediction['student_name'] }}</td>
                <td>{{ $prediction['group_name'] }}</td>
                <td><strong>{{ round($prediction['dropout_probability'] * 100, 1) }}%</strong></td>
                <td class="risk-{{ strtolower($prediction['risk_level']) }}">
                    {{ $prediction['risk_level'] }}
                </td>
                <td>{{ $prediction['recommended_action'] }}</td>
                <td>{{ $prediction['avg_grade'] }}</td>
                <td>{{ $prediction['attendance_rate'] }}%</td>
                <td>{{ round($prediction['payment_regularity'] * 100, 1) }}%</td>
                <td>{{ $prediction['data_status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>