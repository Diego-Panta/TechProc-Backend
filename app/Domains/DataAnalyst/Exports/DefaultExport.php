<?php
// app/Domains/DataAnalyst/Exports/DefaultExport.php

namespace App\Domains\DataAnalyst\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DefaultExport implements WithMultipleSheets
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        return [
            new DefaultSummarySheet($this->data),
        ];
    }
}

class DefaultSummarySheet implements FromArray, WithTitle, WithStyles
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return [
            ['REPORTE DE DATOS ANALÍTICOS'],
            ['Fecha de exportación:', $this->data['export_date'] ?? now()->format('Y-m-d H:i:s')],
            [''],
            ['INFORMACIÓN GENERAL'],
            ['Tipo de reporte:', $this->data['title'] ?? 'Reporte General'],
            [''],
            ['DATOS DISPONIBLES'],
            ...$this->formatAvailableData(),
            [''],
            ['NOTA:'],
            ['Este es un reporte genérico. Para reportes específicos,'],
            ['utilice los endpoints de exportación especializados.'],
        ];
    }

    public function title(): string
    {
        return 'Reporte General';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            4 => ['font' => ['bold' => true]],
            7 => ['font' => ['bold' => true]],
            10 => ['font' => ['italic' => true]],
        ];
    }

    private function formatAvailableData(): array
    {
        $formatted = [];
        $dataKeys = array_keys($this->data);
        
        foreach ($dataKeys as $key) {
            if ($key !== 'title' && $key !== 'export_date' && $key !== 'filters') {
                $count = is_array($this->data[$key]) ? count($this->data[$key]) : 1;
                $formatted[] = [ucfirst(str_replace('_', ' ', $key)) . ':', $count . ' registros'];
            }
        }
        
        if (empty($formatted)) {
            $formatted[] = ['No hay datos específicos disponibles', ''];
        }
        
        return $formatted;
    }
}