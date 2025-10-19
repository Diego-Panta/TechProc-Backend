<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial; font-size: 12px; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #333; }
        .info { margin-bottom: 20px; padding: 10px; background: #f5f5f5; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #333; color: white; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
        .no-data { text-align: center; padding: 20px; color: #666; font-style: italic; }
        .error { color: #d00; background: #fee; padding: 10px; border: 1px solid #d00; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div>Sistema DataAnalyst - TechProc</div>
    </div>
    
    <div class="info">
        <strong>Generado por:</strong> {{ $generatedBy }} | 
        <strong>Fecha:</strong> {{ $generatedAt->format('d/m/Y H:i:s') }}
        @if(isset($data['metadata']['total_records']))
        | <strong>Total registros:</strong> {{ $data['metadata']['total_records'] }}
        @endif
    </div>
    
    <!-- Manejo de errores -->
    @if(isset($data['metadata']['error']))
    <div class="error">
        <strong>Error:</strong> {{ $data['metadata']['error'] }}
    </div>
    @endif

    @if($data['metadata']['report_type'] === 'dashboard')
        <h3>Estadísticas del Sistema</h3>
        <table>
            <tr><th>Indicador</th><th>Valor</th></tr>
            @foreach($data['data'] as $key => $value)
            <tr>
                <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                <td>{{ $value }}</td>
            </tr>
            @endforeach
        </table>
    @elseif(isset($data['data']) && !empty($data['data']))
        <h3>Datos del Reporte</h3>
        
        @php
            // Conversión segura a colección
            $items = $data['data'];
            if (is_array($items)) {
                $items = collect($items);
            }
        @endphp

        @if($items->isNotEmpty())
            @php
                // Obtener headers de forma segura
                $firstItem = $items->first();
                if (is_object($firstItem)) {
                    $headers = array_keys($firstItem->toArray());
                } else {
                    $headers = array_keys((array) $firstItem);
                }
            @endphp
            
            <table>
                <thead>
                    <tr>
                        @foreach($headers as $header)
                            <th>{{ ucwords(str_replace('_', ' ', $header)) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $row)
                        <tr>
                            @php
                                if (is_object($row)) {
                                    $rowData = $row->toArray();
                                } else {
                                    $rowData = (array) $row;
                                }
                            @endphp
                            @foreach($rowData as $value)
                                <td>
                                    @if(is_array($value))
                                        {{ implode(', ', $value) }}
                                    @elseif(is_object($value))
                                        Objeto
                                    @else
                                        {{ $value ?? '-' }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div style="text-align: right; font-style: italic; color: #666; margin-top: 10px;">
                Total de registros: {{ $items->count() }}
            </div>
        @else
            <div class="no-data">
                No hay datos disponibles para mostrar en este reporte
            </div>
        @endif
    @else
        <div class="no-data">
            No hay datos disponibles para mostrar en este reporte
        </div>
    @endif
    
    @if($includeCharts)
    <div style="margin-top: 30px; text-align: center; padding: 20px; border: 1px dashed #ddd;">
        <h3>Gráficos y Visualizaciones</h3>
        <p style="color: #666; font-style: italic;">
            Los gráficos están habilitados para este reporte<br>
            <small>Los gráficos interactivos están disponibles en la versión web del sistema</small>
        </p>
    </div>
    @endif
    
    <div class="footer">
        Generado automáticamente por Sistema DataAnalyst - {{ $generatedAt->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>