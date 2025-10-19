<?php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Domains\DataAnalyst\Services\ExportReportService;
use App\Domains\DataAnalyst\Http\Requests\ExportReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class ExportReportController
{
    protected $exportReportService;

    public function __construct(ExportReportService $exportReportService)
    {
        $this->exportReportService = $exportReportService;
    }

    /**
     * Mostrar página de generación de reportes
     */
    public function index(): View
    {
        $reportTypes = $this->exportReportService->getAvailableReportTypes();

        return view('dataanalyst.export.index', compact('reportTypes'));
    }

    /**
     * Generar y descargar reporte inmediatamente - QUITAR BinaryFileResponse
     */
    public function export(ExportReportRequest $request)
    {
        Log::info('Export request received', $request->validated());

        try {
            // Validar que el tipo de reporte existe
            $validReportTypes = ['students', 'courses', 'attendance', 'grades', 'financial', 'tickets', 'security', 'dashboard'];
            if (!in_array($request->input('report_type'), $validReportTypes)) {
                throw new \Exception('Tipo de reporte no válido');
            }

            $result = $this->exportReportService->generateAndDownloadReport($request->validated());
            Log::info('Export successful - file should be downloading');
            return $result;
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Retornar error como JSON para debugging
            return response()->json([
                'error' => true,
                'message' => 'Error al generar el reporte: ' . $e->getMessage(),
                'report_type' => $request->input('report_type'),
                'format' => $request->input('format')
            ], 500);
        }
    }

    /**
     * Obtener opciones de filtro según tipo de reporte
     */
    public function getFilterOptions($reportType): JsonResponse
    {
        $options = $this->exportReportService->getFilterOptions($reportType);

        return response()->json($options);
    }
}
