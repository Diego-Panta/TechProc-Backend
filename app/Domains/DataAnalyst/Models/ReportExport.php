<?php

namespace App\Domains\DataAnalyst\Models;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportExport implements FromCollection, WithHeadings, WithTitle
{
    protected $data;
    protected $title;

    public function __construct($data, $title)
    {
        $this->data = $data;
        $this->title = $title;
    }

    public function collection()
    {
        // Si es dashboard, retornar datos formateados
        if ($this->data['metadata']['report_type'] === 'dashboard') {
            return collect([$this->data['data']]);
        }

        // Si no hay datos, retornar colección vacía
        if (empty($this->data['data']) || (is_object($this->data['data']) && $this->data['data']->isEmpty())) {
            return collect([['No hay datos disponibles para el reporte']]);
        }

        return $this->data['data'];
    }

    public function headings(): array
    {
        // Si es dashboard, headings especiales
        if ($this->data['metadata']['report_type'] === 'dashboard') {
            return array_keys($this->data['data']);
        }

        // Si no hay datos, retornar un heading por defecto
        if (empty($this->data['data']) || (is_object($this->data['data']) && $this->data['data']->isEmpty())) {
            return ['Información'];
        }

        // Obtener los nombres de las columnas del primer elemento
        $firstItem = is_object($this->data['data']) ? $this->data['data']->first() : $this->data['data'][0];
        
        if (!$firstItem) {
            return ['No hay datos'];
        }

        // Convertir snake_case a Title Case para los headings
        $headings = [];
        foreach (array_keys((array) $firstItem) as $key) {
            $headings[] = $this->formatHeading($key);
        }

        return $headings;
    }

    public function title(): string
    {
        return substr($this->title, 0, 31); // Excel limita a 31 caracteres
    }

    private function formatHeading($heading): string
    {
        // Convertir snake_case a Title Case
        return ucwords(str_replace('_', ' ', $heading));
    }
}