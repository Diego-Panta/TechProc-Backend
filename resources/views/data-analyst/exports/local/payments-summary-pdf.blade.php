{{-- resources/views/data-analyst/exports/local/payments-summary-pdf.blade.php --}}
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
        .table th { background-color: #F0FFF0; font-weight: bold; }
        .filters { margin-bottom: 15px; padding: 8px; background: #e9ecef; border-radius: 5px; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #95a5a6; border-top: 1px solid #ddd; padding-top: 8px; }
        .amount { text-align: right; font-weight: bold; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
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
        <strong>Resumen:</strong> 
        {{ $total_records }} pagos encontrados - 
        Total: ${{ number_format($total_amount, 2) }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>N° Operación</th>
                <th>Estudiante</th>
                <th>Grupo</th>
                <th>Monto</th>
                <th>Estado Pago</th>
                <th>Fecha Operación</th>
                <th>Estado Académico</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->operation_number ?? 'N/A' }}</td>
                <td>{{ $payment->student_name }}</td>
                <td>{{ $payment->group_name }}</td>
                <td class="amount">${{ number_format($payment->amount, 2) }}</td>
                <td class="status-{{ $payment->payment_status }}">
                    {{ ucfirst($payment->payment_status) }}
                </td>
                <td>{{ $payment->operation_date ? \Carbon\Carbon::parse($payment->operation_date)->format('d/m/Y') : '' }}</td>
                <td>{{ $payment->academic_status }}</td>
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