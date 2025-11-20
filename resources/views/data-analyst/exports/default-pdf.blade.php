{{-- resources/views/data-analyst/exports/default-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Reporte General' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .info-box { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #6c757d; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #343a40; color: white; font-weight: bold; }
        .data-section { margin-bottom: 30px; }
        .section-title { background: #6c757d; color: white; padding: 10px; margin: 20px 0 10px 0; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; color: #343a40;">{{ $title ?? 'Reporte de Datos Analíticos' }}</h1>
        <p style="margin: 5px 0; color: #6c757d;">Generado el: {{ $export_date ?? now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <div class="info-box">
        <h3 style="margin-top: 0; color: #495057;">Información del Reporte</h3>
        <p><strong>Tipo:</strong> {{ $title ?? 'Reporte General' }}</p>
        <p><strong>Descripción:</strong> Reporte general que contiene los datos disponibles del sistema de análisis.</p>
    </div>

    @if(!empty($filters))
    <div class="info-box">
        <h3 style="margin-top: 0; color: #495057;">Filtros Aplicados</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
            @foreach($filters as $key => $value)
                <div>
                    <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> 
                    <span>{{ $value }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @php
        $dataSections = [];
        foreach($data as $key => $section) {
            if(is_array($section) && !in_array($key, ['title', 'export_date', 'filters', 'summary']) && count($section) > 0) {
                $dataSections[$key] = $section;
            }
        }
    @endphp

    @if(!empty($dataSections))
        @foreach($dataSections as $sectionKey => $sectionData)
            <div class="data-section">
                <div class="section-title">
                    <h3 style="margin: 0; font-size: 14px;">
                        {{ ucfirst(str_replace('_', ' ', $sectionKey)) }} 
                        ({{ count($sectionData) }} registros)
                    </h3>
                </div>
                
                @if(isset($sectionData[0]) && is_array($sectionData[0]))
                    <table class="table">
                        <thead>
                            <tr>
                                @foreach(array_keys($sectionData[0]) as $column)
                                    <th>{{ ucfirst(str_replace('_', ' ', $column)) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sectionData as $row)
                                <tr>
                                    @foreach($row as $value)
                                        <td>
                                            @if(is_numeric($value) && (strpos($column, 'rate') !== false || strpos($column, 'percentage') !== false))
                                                {{ $value }}%
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 5px;">
                        <pre>{{ json_encode($sectionData, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif
            </div>
        @endforeach
    @else
    <div class="info-box">
        <h3 style="margin-top: 0; color: #495057;">Sin Datos Específicos</h3>
        <p>No se encontraron datos específicos para mostrar en este reporte.</p>
        <p>Utilice los endpoints especializados para obtener reportes detallados de:</p>
        <ul>
            <li><strong>Asistencia:</strong> Métricas de participación en sesiones</li>
            <li><strong>Progreso:</strong> Avance en módulos y consistencia de calificaciones</li>
            <li><strong>Rendimiento:</strong> Resultados académicos y tasas de aprobación</li>
        </ul>
    </div>
    @endif

    <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #dee2e6; text-align: center; color: #6c757d;">
        <p>Sistema de Análisis de Datos - Generado automáticamente</p>
    </div>
</body>
</html>