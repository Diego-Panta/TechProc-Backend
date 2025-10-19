<!DOCTYPE html>
<html>
<head>
    <title>Reporte Financiero - Data Analyst</title>
</head>
<body>
    <h1>Reporte Financiero</h1>
    
    <!-- Filtros -->
    <form method="GET" action="/data-analyst/financial">
        <div>
            <label>Fecha Inicio:</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}">
        </div>
        <div>
            <label>Fecha Fin:</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}">
        </div>
        <div>
            <label>Fuente de Ingresos:</label>
            <select name="revenue_source_id">
                <option value="">Todas las fuentes</option>
                @foreach($revenueSources as $source)
                <option value="{{ $source->id }}" 
                    {{ request('revenue_source_id') == $source->id ? 'selected' : '' }}>
                    {{ $source->name }}
                </option>
                @endforeach
            </select>
        </div>
        <button type="submit">Filtrar</button>
        <a href="/data-analyst/financial">Limpiar Filtros</a>
    </form>

    <!-- Fechas aplicadas -->
    @if(!empty($financialData['filters_applied']['start_date']) || !empty($financialData['filters_applied']['end_date']))
    <div>
        <h3>Filtros Aplicados:</h3>
        <p>
            @if(!empty($financialData['filters_applied']['start_date']))
            Desde: {{ $financialData['filters_applied']['start_date'] }}
            @endif
            @if(!empty($financialData['filters_applied']['end_date']))
            Hasta: {{ $financialData['filters_applied']['end_date'] }}
            @endif
            @if(!empty($financialData['filters_applied']['revenue_source_id']))
            | Fuente: 
            @foreach($revenueSources as $source)
                @if($source->id == $financialData['filters_applied']['revenue_source_id'])
                    {{ $source->name }}
                @endif
            @endforeach
            @endif
        </p>
    </div>
    @endif

    <!-- Resumen Financiero -->
    <div>
        <h2>Resumen Financiero</h2>
        <p><strong>Ingresos Totales:</strong> ${{ number_format($financialData['total_revenue'] ?? 0, 2) }}</p>
        <p><strong>Gastos Totales:</strong> ${{ number_format($financialData['total_expenses'] ?? 0, 2) }}</p>
        <p><strong>Ingreso Neto:</strong> ${{ number_format($financialData['net_income'] ?? 0, 2) }}</p>
    </div>

    <!-- Ingresos por Fuente -->
    <div>
        <h2>Ingresos por Fuente</h2>
        @if(count($financialData['by_revenue_source'] ?? []) > 0)
        <table border="1">
            <thead>
                <tr>
                    <th>Fuente</th>
                    <th>Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($financialData['by_revenue_source'] as $source)
                <tr>
                    <td>{{ $source['source_name'] }}</td>
                    <td>${{ number_format($source['amount'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No hay datos para mostrar con los filtros aplicados.</p>
        @endif
    </div>

    <!-- Pagos Pendientes -->
    <div>
        <h2>Pagos Pendientes</h2>
        <p><strong>Cantidad:</strong> {{ $financialData['pending_payments']['count'] ?? 0 }}</p>
        <p><strong>Monto Total Pendiente:</strong> ${{ number_format($financialData['pending_payments']['total_amount'] ?? 0, 2) }}</p>
    </div>

    <!-- Métricas Adicionales -->
    <div>
        <h2>Métricas Adicionales</h2>
        <p><strong>Tasa de Cobranza:</strong> {{ $financialData['additional_metrics']['collection_rate'] ?? 0 }}%</p>
        <p><strong>Facturas Pagadas:</strong> {{ $financialData['additional_metrics']['paid_invoices'] ?? 0 }}</p>
        <p><strong>Total Facturas:</strong> {{ $financialData['additional_metrics']['total_invoices'] ?? 0 }}</p>
        <p><strong>Factura Promedio:</strong> ${{ number_format($financialData['additional_metrics']['average_invoice_amount'] ?? 0, 2) }}</p>
    </div>

    <!-- Enlaces a APIs -->
    <div>
        <h3>Accesos Directos a Datos:</h3>
        <a href="/data-analyst/financial/statistics?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}&revenue_source_id={{ request('revenue_source_id') }}" target="_blank">
            Ver Estadísticas en JSON
        </a>
        <br>
        <a href="/data-analyst/financial/revenue-trend?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}&revenue_source_id={{ request('revenue_source_id') }}" target="_blank">
            Ver Tendencia de Ingresos en JSON
        </a>
        <br>
        <a href="/data-analyst/financial/revenue-sources" target="_blank">
            Ver Fuentes de Ingresos en JSON
        </a>
    </div>

    <!-- Tendencia de Ingresos (vista simple) -->
    <div>
        <h2>Tendencia de Ingresos</h2>
        @if(count($financialData['revenue_trend'] ?? []) > 0)
        <table border="1">
            <thead>
                <tr>
                    <th>Mes</th>
                    <th>Ingresos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($financialData['revenue_trend'] as $trend)
                <tr>
                    <td>{{ $trend['month'] }}</td>
                    <td>${{ number_format($trend['revenue'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No hay datos de tendencia para mostrar.</p>
        @endif
    </div>
</body>
</html>