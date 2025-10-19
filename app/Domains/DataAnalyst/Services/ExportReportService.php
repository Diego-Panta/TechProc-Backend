<?php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Repositories\ExportReportRepository;
use App\Domains\DataAnalyst\Models\ReportExport;
use App\Domains\Administrator\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Log;

class ExportReportService
{
    protected $exportReportRepository;

    public function __construct(ExportReportRepository $exportReportRepository)
    {
        $this->exportReportRepository = $exportReportRepository;
    }

    /**
     * Generar y descargar reporte inmediatamente
     */
    public function generateAndDownloadReport(array $data)
    {
        Log::info("=== INICIANDO GENERACIÓN DE REPORTE ===", $data);

        $reportType = $data['report_type'];
        $format = $data['format'];

        // PROCESAR FILTROS - MEJORADO
        $filters = $data['filters'] ?? [];

        // Si las fechas vienen en el nivel raíz, agregarlas a filters
        if (isset($data['start_date']) && !empty($data['start_date'])) {
            $filters['start_date'] = $data['start_date'];
        }
        if (isset($data['end_date']) && !empty($data['end_date'])) {
            $filters['end_date'] = $data['end_date'];
        }

        $includeCharts = $data['include_charts'] ?? false;
        $includeRawData = $data['include_raw_data'] ?? false;
        $reportTitle = $data['report_title'] ?? $this->generateDefaultTitle($reportType, $filters);

        // DEBUG TEMPORAL: Verificar estructura de datos
        Log::debug("=== ESTRUCTURA DE DATOS RECIBIDA ===", [
            'report_type' => $reportType,
            'format' => $format,
            'filters' => $filters,
            'filters_count' => count($filters),
            'filters_keys' => array_keys($filters),
            'filters_values' => array_values($filters),
            'include_charts' => $includeCharts,
            'include_raw_data' => $includeRawData,
            'report_title' => $reportTitle,
            'data_original' => $data
        ]);

        // Validar fechas - MEJORADO
        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            try {
                if (!empty($filters['start_date'])) {
                    $start = Carbon::parse($filters['start_date'])->startOfDay();
                    Log::debug("Fecha inicio parseada: {$start}");
                }

                if (!empty($filters['end_date'])) {
                    $end = Carbon::parse($filters['end_date'])->endOfDay();
                    Log::debug("Fecha fin parseada: {$end}");
                }

                if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                    if ($end->lt($start)) {
                        throw new \Exception("La fecha final no puede ser anterior a la fecha inicial");
                    }

                    Log::debug("Rango de fechas válido: {$start} a {$end}");
                }
            } catch (\Exception $e) {
                Log::error("Error validando fechas: " . $e->getMessage());
                throw new \Exception("Formato de fecha inválido: " . $e->getMessage());
            }
        }

        try {
            // Validar tipo de reporte
            $validTypes = ['students', 'courses', 'attendance', 'grades', 'financial', 'tickets', 'security', 'dashboard'];
            if (!in_array($reportType, $validTypes)) {
                throw new \Exception("Tipo de reporte no válido: {$reportType}");
            }

            // Obtener datos del reporte
            Log::debug("Obteniendo datos del repositorio...");
            $reportData = $this->exportReportRepository->getReportData($reportType, $filters);
            Log::debug("Datos obtenidos", ['tipo' => $reportType, 'total' => $reportData['metadata']['total_records'] ?? 0]);

            // Validar estructura de datos
            if (!isset($reportData['data']) || !isset($reportData['metadata'])) {
                throw new \Exception("Estructura de datos del reporte inválida");
            }

            if ($format === 'excel') {
                Log::debug("Generando Excel...");
                return $this->generateExcelDownload($reportData, $reportTitle, $reportType);
            } else {
                Log::debug("Generando PDF...");
                return $this->generatePdfDownload($reportData, $reportTitle, $includeCharts, $includeRawData);
            }
        } catch (\Exception $e) {
            Log::error("ERROR en generateAndDownloadReport: " . $e->getMessage(), [
                'report_type' => $reportType,
                'format' => $format,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Generar y descargar Excel
     */
    private function generateExcelDownload($reportData, $title, $reportType): BinaryFileResponse
    {
        try {
            $fileName = $this->generateFileName($reportType, 'xlsx');
            Log::debug("Generando Excel: {$fileName}");

            $export = new ReportExport($reportData, $title);

            Log::debug("Excel listo para descargar");
            return Excel::download($export, $fileName);
        } catch (\Exception $e) {
            Log::error("ERROR en generateExcelDownload: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generar y descargar PDF
     */
    private function generatePdfDownload($reportData, $title, $includeCharts, $includeRawData)
    {
        try {
            $fileName = $this->generateFileName('report', 'pdf');
            Log::debug("Generando PDF: {$fileName}");

            $generatedBy = $this->getFirstUserName();

            Log::debug("Cargando vista PDF...");
            $pdf = Pdf::loadView('dataanalyst.export.templates.pdf', [
                'data' => $reportData,
                'title' => $title,
                'includeCharts' => $includeCharts,
                'includeRawData' => $includeRawData,
                'generatedAt' => Carbon::now(),
                'generatedBy' => $generatedBy
            ]);

            Log::debug("PDF generado correctamente");
            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error("ERROR en generatePdfDownload: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener el nombre del usuario de forma segura
     */
    private function getFirstUserName(): string
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user && $user->name) {
                    return (string) $user->name;
                }
            }

            $firstUser = User::first();
            if ($firstUser && $firstUser->name) {
                return (string) $firstUser->name;
            }

            return 'Administrador del Sistema';
        } catch (\Exception $e) {
            Log::warning("Error en getFirstUserName: " . $e->getMessage());
            return 'Sistema DataAnalyst';
        }
    }

    /**
     * Obtener tipos de reporte disponibles
     */
    public function getAvailableReportTypes(): array
    {
        return [
            'students' => [
                'name' => 'Estudiantes',
                'description' => 'Reporte completo de estudiantes matriculados',
                'icon' => 'users'
            ],
            'courses' => [
                'name' => 'Cursos',
                'description' => 'Análisis de cursos y su rendimiento',
                'icon' => 'book'
            ],
            'attendance' => [
                'name' => 'Asistencia',
                'description' => 'Reporte de asistencia y participación',
                'icon' => 'calendar'
            ],
            'grades' => [
                'name' => 'Calificaciones',
                'description' => 'Análisis de rendimiento académico',
                'icon' => 'chart-bar'
            ],
            'financial' => [
                'name' => 'Financiero',
                'description' => 'Reporte de ingresos y transacciones',
                'icon' => 'currency-dollar'
            ],
            'tickets' => [
                'name' => 'Tickets de Soporte',
                'description' => 'Análisis de tickets y tiempos de respuesta',
                'icon' => 'ticket'
            ],
            'security' => [
                'name' => 'Seguridad',
                'description' => 'Reporte de eventos y alertas de seguridad',
                'icon' => 'shield-check'
            ],
            'dashboard' => [
                'name' => 'Dashboard General',
                'description' => 'Resumen general del sistema',
                'icon' => 'chart-pie'
            ]
        ];
    }

    /**
     * Obtener opciones de filtro por tipo de reporte
     */
    public function getFilterOptions($reportType): array
    {
        Log::debug("Solicitando opciones de filtro para: {$reportType}");
        $options = $this->exportReportRepository->getFilterOptions($reportType);
        Log::debug("Opciones retornadas", $options);
        return $options;
    }

    /**
     * Generar nombre de archivo
     */
    private function generateFileName($reportType, $format): string
    {
        $timestamp = Carbon::now()->format('Y-m-d_His');
        return "{$reportType}_report_{$timestamp}.{$format}";
    }

    /**
     * Generar título por defecto
     */
    private function generateDefaultTitle($reportType, $filters): string
    {
        $titles = [
            'students' => 'Reporte de Estudiantes',
            'courses' => 'Reporte de Cursos',
            'attendance' => 'Reporte de Asistencia',
            'grades' => 'Reporte de Calificaciones',
            'financial' => 'Reporte Financiero',
            'tickets' => 'Reporte de Tickets',
            'security' => 'Reporte de Seguridad',
            'dashboard' => 'Dashboard General'
        ];

        $title = $titles[$reportType] ?? 'Reporte del Sistema';

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $start = Carbon::parse($filters['start_date'])->format('d/m/Y');
            $end = Carbon::parse($filters['end_date'])->format('d/m/Y');
            $title .= " - {$start} al {$end}";
        }

        return $title;
    }

    public function previewReportData(string $reportType, array $filters = [])
    {
        Log::debug("Generando vista previa para: {$reportType}", $filters);

        // Validar tipo de reporte
        $validTypes = ['students', 'courses', 'attendance', 'grades', 'financial', 'tickets', 'security', 'dashboard'];
        if (!in_array($reportType, $validTypes)) {
            throw new \Exception("Tipo de reporte no válido: {$reportType}");
        }

        // Obtener datos del reporte
        $reportData = $this->exportReportRepository->getReportData($reportType, $filters);

        // Validar estructura de datos
        if (!isset($reportData['data']) || !isset($reportData['metadata'])) {
            throw new \Exception("Estructura de datos del reporte inválida");
        }

        Log::debug("Vista previa generada", [
            'tipo' => $reportType,
            'total' => $reportData['metadata']['total_records'] ?? 0
        ]);

        return $reportData;
    }
}
