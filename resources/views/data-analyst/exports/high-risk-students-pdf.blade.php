{{-- resources/views/data-analyst/exports/high-risk-students-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #ff6b6b; padding-bottom: 10px; }
        .alert-banner { background: #ff6b6b; color: white; padding: 15px; text-align: center; margin-bottom: 20px; border-radius: 5px; }
        .summary { margin-bottom: 20px; padding: 15px; background: #fff0f0; border: 2px solid #ff6b6b; border-radius: 5px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #ff6b6b; color: white; font-weight: bold; }
        .critical { background: #ff6b6b; color: white; font-weight: bold; }
        .high { background: #ff8e8e; color: white; font-weight: bold; }
        .medium { background: #ffb3b3; font-weight: bold; }
        .action-plan { background: #e7f3ff; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .urgency-critical { background: #ff0000; color: white; font-weight: bold; padding: 3px 8px; border-radius: 3px; }
        .urgency-high { background: #ff6b6b; color: white; font-weight: bold; padding: 3px 8px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="color: #ff6b6b;">{{ $title }}</h1>
        <p>Generado el: {{ $export_date }}</p>
    </div>

    <div class="alert-banner">
        <h2>🚨 ALERTA CRÍTICA - INTERVENCIÓN INMEDIATA REQUERIDA</h2>
        <p>Se han identificado {{ count($high_risk_students) }} estudiantes con alto riesgo de deserción</p>
    </div>

    <div class="summary">
        <h3>RESUMEN DE URGENCIA</h3>
        <p><strong>Total estudiantes críticos:</strong> {{ count($high_risk_students) }}</p>
        <p><strong>Acción requerida:</strong> Contacto inmediato dentro de 24-48 horas</p>
        <p><strong>Prioridad:</strong> Máxima - Requiere seguimiento personalizado</p>
    </div>

    @if(!empty($high_risk_students))
    <h3>LISTA DE ESTUDIANTES CRÍTICOS</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Estudiante</th>
                <th>Grupo</th>
                <th>Probabilidad</th>
                <th>Urgencia</th>
                <th>Nota</th>
                <th>Asistencia</th>
                <th>Pagos</th>
                <th>Últ. Pago</th>
                <th>Acción Inmediata</th>
            </tr>
        </thead>
        <tbody>
            @foreach($high_risk_students as $student)
            <tr>
                <td><strong>{{ $student['student_name'] }}</strong></td>
                <td>{{ $student['group_name'] }}</td>
                <td class="critical"><strong>{{ round(($student['dropout_probability'] ?? $student['riesgo_porcentaje'] ?? 0), 1) }}%</strong></td>
                <td>
                    @php
                        $probability = $student['dropout_probability'] ?? $student['riesgo_porcentaje'] ?? 0;
                        $urgencyClass = $probability >= 80 ? 'urgency-critical' : 'urgency-high';
                        $urgencyText = $probability >= 80 ? 'CRÍTICO' : 'ALTO';
                    @endphp
                    <span class="{{ $urgencyClass }}">{{ $urgencyText }}</span>
                </td>
                <td>{{ $student['avg_grade'] ?? 0 }}</td>
                <td>{{ $student['attendance_rate'] ?? 0 }}%</td>
                <td>{{ round(($student['payment_regularity'] ?? 0) * 100, 1) }}%</td>
                <td>{{ $student['days_since_last_payment'] ?? 0 }} días</td>
                <td>{{ $student['accion_recomendada'] ?? $student['recommended_action'] ?? 'CONTACTO INMEDIATO' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="action-plan">
        <h3>📋 PLAN DE ACCIÓN INMEDIATO</h3>
        <p><strong>Primeras 24 horas:</strong></p>
        <ul>
            <li>Contacto telefónico directo con todos los estudiantes listados</li>
            <li>Evaluación de situación académica, económica y personal</li>
            <li>Asignación de tutor académico para seguimiento personalizado</li>
        </ul>
        
        <p><strong>Siguientes 48 horas:</strong></p>
        <ul>
            <li>Establecimiento de plan de acción individualizado</li>
            <li>Coordinación con área económica para flexibilización de pagos</li>
            <li>Programación de sesiones de tutoría semanales</li>
        </ul>
        
        <p><strong>Seguimiento semanal:</strong></p>
        <ul>
            <li>Revisión de métricas de progreso (asistencia, rendimiento, pagos)</li>
            <li>Ajuste de estrategias según evolución</li>
            <li>Comunicación constante con estudiante y familia</li>
        </ul>
    </div>
</body>
</html>