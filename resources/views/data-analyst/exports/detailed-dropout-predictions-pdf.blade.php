{{-- resources/views/data-analyst/exports/detailed-dropout-predictions-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .summary { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        .filters { margin-bottom: 20px; padding: 10px; background: #e9ecef; }
        .risk-high { background: #ff6b6b; color: white; font-weight: bold; }
        .risk-medium { background: #ffd93d; font-weight: bold; }
        .risk-low { background: #6bcf7f; color: white; font-weight: bold; }
        .section { margin-bottom: 25px; }
        .section-title { background: #343a40; color: white; padding: 8px; margin: 15px 0 10px 0; }
        .analysis-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px; }
        .analysis-card { padding: 12px; border: 1px solid #ddd; border-radius: 5px; background: #f8f9fa; }
        .text-limit { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
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

    @if(!empty($analysis))
    <div class="section">
        <div class="section-title">RESUMEN EJECUTIVO</div>
        <div class="analysis-grid">
            <div class="analysis-card">
                <h4>Distribución de Riesgo</h4>
                <p><strong>Alto Riesgo:</strong> {{ $analysis['risk_distribution']['ALTO'] ?? 0 }}</p>
                <p><strong>Riesgo Medio:</strong> {{ $analysis['risk_distribution']['MEDIO'] ?? 0 }}</p>
                <p><strong>Bajo Riesgo:</strong> {{ $analysis['risk_distribution']['BAJO'] ?? 0 }}</p>
                <p><strong>Total:</strong> {{ $analysis['total'] ?? 0 }}</p>
            </div>
            <div class="analysis-card">
                <h4>Métricas Clave</h4>
                <p><strong>Nota Prom. Alto Riesgo:</strong> {{ $analysis['performance_insights']['avg_metrics_high_risk']['avg_grade'] ?? 0 }}/20</p>
                <p><strong>Asistencia Prom. Alto Riesgo:</strong> {{ $analysis['performance_insights']['avg_metrics_high_risk']['attendance_rate'] ?? 0 }}%</p>
                <p><strong>Pagos Prom. Alto Riesgo:</strong> {{ $analysis['performance_insights']['avg_metrics_high_risk']['payment_regularity'] ?? 0 }}%</p>
            </div>
        </div>
    </div>
    @endif

    @if(!empty($students))
    <div class="section">
        <div class="section-title">DETALLE DE ESTUDIANTES (Primeros 50 registros)</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Grupo</th>
                    <th>Prob.</th>
                    <th>Riesgo</th>
                    <th>Nota</th>
                    <th>Asist.</th>
                    <th>Pagos</th>
                    <th>Progreso</th>
                    <th>Recomendación</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($students, 0, 50) as $student)
                <tr>
                    <td>{{ $student['student_name'] }}</td>
                    <td>{{ $student['group_name'] }}</td>
                    <td><strong>{{ round($student['dropout_probability'] * 100, 1) }}%</strong></td>
                    <td class="risk-{{ strtolower($student['risk_level']) }}">
                        {{ $student['risk_level'] }}
                    </td>
                    <td>{{ $student['avg_grade'] }}</td>
                    <td>{{ $student['attendance_rate'] }}%</td>
                    <td>{{ round($student['payment_regularity'] * 100, 1) }}%</td>
                    <td>{{ round($student['course_progress'] * 100, 1) }}%</td>
                    <td class="text-limit" title="{{ $student['recommendation'] }}">
                        {{ strlen($student['recommendation']) > 30 ? substr($student['recommendation'], 0, 30) . '...' : $student['recommendation'] }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if(count($students) > 50)
        <p><em>Mostrando 50 de {{ count($students) }} registros. Consulte el archivo Excel para ver todos los datos.</em></p>
        @endif
    </div>
    @endif

    @if(!empty($analysis['common_risk_factors']))
    <div class="section">
        <div class="section-title">FACTORES DE RIESGO PRINCIPALES</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Factor de Riesgo</th>
                    <th>Estudiantes Afectados</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analysis['common_risk_factors'] as $factor => $count)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $factor)) }}</td>
                    <td>{{ $count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</body>
</html>