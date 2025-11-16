<?php
// app/Domains/DataAnalyst/Exports/Local/DefaultExport.php

namespace App\Domains\DataAnalyst\Exports\Local;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DefaultExport implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return [
            ['Información del Reporte', ''],
            ['Título', $this->data['title'] ?? 'Reporte no especificado'],
            ['Fecha de Exportación', $this->data['export_date'] ?? ''],
            ['Total de Registros', $this->data['total_records'] ?? 0],
            ['', ''],
            ['Mensaje', 'No se encontró una exportación específica para este tipo de reporte.'],
            ['', ''],
            ['Filtros Aplicados', ''],
            ...$this->formatFilters()
        ];
    }

    private function formatFilters(): array
    {
        $filters = $this->data['filters'] ?? [];
        $formatted = [];
        
        foreach ($filters as $key => $value) {
            $formatted[] = [ucfirst(str_replace('_', ' ', $key)), $value];
        }
        
        if (empty($formatted)) {
            $formatted[] = ['No se aplicaron filtros', ''];
        }
        
        return $formatted;
    }

    public function headings(): array
    {
        return [
            'Campo',
            'Valor'
        ];
    }

    public function title(): string
    {
        return 'Reporte General';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            6 => ['font' => ['italic' => true]],
            8 => ['font' => ['bold' => true]],
            'A1:B1' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E6E6FA']]],
            'A2:B4' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F5F5F5']]],
        ];
    }
}