{{-- resources/views/data-analyst/exports/local/attendance-summary-pdf.blade.php --}}
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
        .table th { background-color: #F0F8FF; font-weight: bold; }
        .filters { margin-bottom: 15px; padding: 8px; background: #e9ecef; border-radius: 5px; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #95a5a6; border-top: 1px solid #ddd; padding-top: 8px; }
        .attendance-excellent { background-color: #d4edda; color: #155724; }
        .attendance-good { background-color: #fff3cd; color: #856404; }
        .attendance-poor { background-color: #f8d7da; color: #721c24; }
        .statistics { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px; }
        .stat-card { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 10px; text-align: center; }
        .stat-value { font-size: 18px; font-weight: bold; color: #2c3e50; }
        .stat-label { font-size: 10px; color: #6c757d; text-transform: uppercase; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
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

    @php
        $totalPresent = $attendance->sum('present_count');
        $totalAbsent = $attendance->sum('absent_count');
        $totalLate = $attendance->sum('late_count');
        $totalSessions = $attendance->sum('total_sessions');
        $averageAttendance = $totalSessions > 0 ? round(($totalPresent / $totalSessions) * 100, 2) : 0;
    @endphp

    <div class="statistics">
        <div class="stat-card">
            <div class="stat-value">{{ $totalSessions }}</div>
            <div class="stat-label">Total Sesiones</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $totalPresent }}</div>
            <div class="stat-label">Asistencias</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $averageAttendance }}%</div>
            <div class="stat-label">Promedio Asistencia</div>
        </div>
    </div>

    <div class="summary">
        <strong>Resumen:</strong> {{ $total_records }} registros de asistencia encontrados
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Grupo</th>
                <th>Estudiante</th>
                <th class="text-center">Total Sesiones</th>
                <th class="text-center">Presente</th>
                <th class="text-center">Ausente</th>
                <th class="text-center">Tardío</th>
                <th class="text-center">Tasa Asistencia</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendance as $record)
            @php
                $attendanceRate = $record->attendance_rate ?? 0;
                $statusClass = 'attendance-';
                if ($attendanceRate >= 90) {
                    $statusClass .= 'excellent';
                } elseif ($attendanceRate >= 75) {
                    $statusClass .= 'good';
                } else {
                    $statusClass .= 'poor';
                }
            @endphp
            <tr>
                <td>{{ $record->group_name }}</td>
                <td>{{ $record->student_name }}</td>
                <td class="text-center">{{ $record->total_sessions ?? 0 }}</td>
                <td class="text-center">{{ $record->present_count ?? 0 }}</td>
                <td class="text-center">{{ $record->absent_count ?? 0 }}</td>
                <td class="text-center">{{ $record->late_count ?? 0 }}</td>
                <td class="text-center {{ $statusClass }}">
                    {{ $attendanceRate }}%
                </td>
                <td class="text-center {{ $statusClass }}">
                    @if($attendanceRate >= 90)
                        Excelente
                    @elseif($attendanceRate >= 75)
                        Bueno
                    @else
                        Bajo
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($attendance->count() > 0)
    <div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
        <strong>Estadísticas Generales:</strong><br>
        • Total de sesiones registradas: {{ $totalSessions }}<br>
        • Total de asistencias: {{ $totalPresent }} ({{ $totalSessions > 0 ? round(($totalPresent / $totalSessions) * 100, 2) : 0 }}%)<br>
        • Total de ausencias: {{ $totalAbsent }} ({{ $totalSessions > 0 ? round(($totalAbsent / $totalSessions) * 100, 2) : 0 }}%)<br>
        • Total de llegadas tardías: {{ $totalLate }} ({{ $totalSessions > 0 ? round(($totalLate / $totalSessions) * 100, 2) : 0 }}%)<br>
        • Promedio general de asistencia: {{ $averageAttendance }}%
    </div>
    @endif

    <div class="footer">
        Reporte generado automáticamente por el Sistema DataAnalyst<br>
        {{ config('app.name') }} - {{ date('Y') }}
    </div>
</body>
</html>