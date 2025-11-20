<?php
// app/Domains/DataAnalyst/Exports/Local/SupportTicketsExport.php

namespace App\Domains\DataAnalyst\Exports\Local;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupportTicketsExport implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data['tickets']->map(function ($ticket) {
            return [
                $ticket->ticket_id ?? '',
                $ticket->ticket_title ?? '',
                $ticket->ticket_type ?? '',
                $ticket->ticket_status ?? '',
                $ticket->ticket_priority ?? '',
                $ticket->student_name ?? '',
                $ticket->group_name ?? '',
                $ticket->created_at ? \Carbon\Carbon::parse($ticket->created_at)->format('d/m/Y H:i') : ''
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID Ticket',
            'Título',
            'Tipo',
            'Estado',
            'Prioridad',
            'Estudiante',
            'Grupo',
            'Fecha Creación'
        ];
    }

    public function title(): string
    {
        return 'Tickets de Soporte';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A1:H1' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E6F3FF']]],
        ];
    }
}