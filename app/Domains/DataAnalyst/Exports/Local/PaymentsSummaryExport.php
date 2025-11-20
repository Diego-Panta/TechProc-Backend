<?php
// app/Domains/DataAnalyst/Exports/Local/PaymentsSummaryExport.php

namespace App\Domains\DataAnalyst\Exports\Local;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsSummaryExport implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data['payments']->map(function ($payment) {
            return [
                $payment->operation_number ?? '',
                $payment->student_name ?? '',
                $payment->group_name ?? '',
                number_format($payment->amount, 2),
                $payment->payment_status ?? '',
                $payment->operation_date ? \Carbon\Carbon::parse($payment->operation_date)->format('d/m/Y') : '',
                $payment->academic_status ?? ''
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Número Operación',
            'Estudiante',
            'Grupo',
            'Monto',
            'Estado Pago',
            'Fecha Operación',
            'Estado Académico'
        ];
    }

    public function title(): string
    {
        return 'Pagos';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A1:G1' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F0FFF0']]],
        ];
    }
}