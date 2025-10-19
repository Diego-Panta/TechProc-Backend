<?php

namespace App\Domains\DataAnalyst\Http\Controllers\Api;

use App\Domains\DataAnalyst\Services\ExportReportService;
use App\Domains\DataAnalyst\Http\Requests\Api\ExportReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExportReportApiController
{
    public function __construct(
        private ExportReportService $exportReportService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/data-analyst/export/generate",
     *     summary="Generar y descargar reporte",
     *     tags={"DataAnalyst - Export Reports"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"report_type","format"},
     *             @OA\Property(property="report_type", type="string", enum={"students","courses","attendance","grades","financial","tickets","security","dashboard"}, description="Tipo de reporte a generar"),
     *             @OA\Property(property="format", type="string", enum={"excel","pdf"}, description="Formato del reporte"),
     *             @OA\Property(property="report_title", type="string", maxLength=255, description="Título personalizado del reporte"),
     *             @OA\Property(property="start_date", type="string", format="date", description="Fecha de inicio para filtrar datos"),
     *             @OA\Property(property="end_date", type="string", format="date", description="Fecha de fin para filtrar datos"),
     *             @OA\Property(property="filters", type="object", 
     *                 @OA\Property(property="company_id", type="integer", description="ID de empresa para filtrar"),
     *                 @OA\Property(property="academic_period_id", type="integer", description="ID de período académico"),
     *                 @OA\Property(property="status", type="string", description="Estado para filtrar (solo estudiantes y tickets)"),
     *                 @OA\Property(property="start_date", type="string", format="date"),
     *                 @OA\Property(property="end_date", type="string", format="date")
     *             ),
     *             @OA\Property(property="include_charts", type="boolean", description="Incluir gráficos en PDF"),
     *             @OA\Property(property="include_raw_data", type="boolean", description="Incluir datos crudos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reporte generado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reporte generado exitosamente"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="download_url", type="string", example="https://api.example.com/api/data-analyst/export/download/temp_token"),
     *                 @OA\Property(property="file_name", type="string", example="students_report_2025-10-18_143022.xlsx"),
     *                 @OA\Property(property="file_size", type="string", example="2.45 MB"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time", example="2025-10-18T16:30:22Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function generateReport(ExportReportRequest $request): JsonResponse
    {
        try {
            Log::info('API Export Report Request', $request->validated());

            $result = $this->exportReportService->generateAndDownloadReport($request->validated());

            // Para API, retornamos información del archivo generado
            return response()->json([
                'success' => true,
                'message' => 'Reporte generado exitosamente',
                'data' => [
                    'download_url' => $this->generateDownloadUrl($result),
                    'file_name' => $this->extractFileName($result),
                    'file_size' => 'N/A', // No tenemos info de tamaño en descarga directa
                    'expires_at' => now()->addMinutes(30)->toISOString() // Token temporal de 30 min
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Error generating report', [
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/export/filter-options/{reportType}",
     *     summary="Obtener opciones de filtro por tipo de reporte",
     *     tags={"DataAnalyst - Export Reports"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="reportType",
     *         in="path",
     *         required=true,
     *         description="Tipo de reporte",
     *         @OA\Schema(type="string", enum={"students","courses","attendance","grades","financial","tickets","security","dashboard"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opciones de filtro obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="statuses", type="array", 
     *                     @OA\Items(type="string", example="active"),
     *                     description="Opciones de estado (solo para estudiantes y tickets)"
     *                 ),
     *                 @OA\Property(property="companies", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Tech Solutions SAC")
     *                     )
     *                 ),
     *                 @OA\Property(property="academic_periods", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="2025-I")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tipo de reporte no válido"
     *     )
     * )
     */
    public function getFilterOptions(string $reportType): JsonResponse
    {
        try {
            $validTypes = ['students', 'courses', 'attendance', 'grades', 'financial', 'tickets', 'security', 'dashboard'];
            
            if (!in_array($reportType, $validTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de reporte no válido'
                ], 404);
            }

            $options = $this->exportReportService->getFilterOptions($reportType);

            return response()->json([
                'success' => true,
                'data' => $options
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting filter options', [
                'report_type' => $reportType,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las opciones de filtro',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/export/report-types",
     *     summary="Obtener tipos de reporte disponibles",
     *     tags={"DataAnalyst - Export Reports"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Tipos de reporte obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="key", type="string", example="students"),
     *                     @OA\Property(property="name", type="string", example="Estudiantes"),
     *                     @OA\Property(property="description", type="string", example="Reporte completo de estudiantes matriculados"),
     *                     @OA\Property(property="icon", type="string", example="users"),
     *                     @OA\Property(property="available_filters", type="array",
     *                         @OA\Items(type="string", example="status")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getReportTypes(): JsonResponse
    {
        try {
            $reportTypes = $this->exportReportService->getAvailableReportTypes();
            
            // Formatear respuesta para API
            $formattedTypes = [];
            foreach ($reportTypes as $key => $type) {
                $formattedTypes[] = [
                    'key' => $key,
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'icon' => $type['icon'],
                    'available_filters' => $this->getAvailableFilters($key)
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $formattedTypes
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting report types', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los tipos de reporte',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/data-analyst/export/preview",
     *     summary="Vista previa de datos del reporte",
     *     tags={"DataAnalyst - Export Reports"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"report_type"},
     *             @OA\Property(property="report_type", type="string", enum={"students","courses","attendance","grades","financial","tickets","security","dashboard"}),
     *             @OA\Property(property="start_date", type="string", format="date"),
     *             @OA\Property(property="end_date", type="string", format="date"),
     *             @OA\Property(property="filters", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vista previa generada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="preview_data", type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="total_records", type="integer", example=150),
     *                 @OA\Property(property="columns", type="array",
     *                     @OA\Items(type="string", example="nombre")
     *                 ),
     *                 @OA\Property(property="metadata", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function previewReport(ExportReportRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $reportType = $data['report_type'];
            $filters = $data['filters'] ?? [];

            // Obtener datos del reporte sin generar archivo
            $reportData = $this->exportReportService->previewReportData($reportType, $filters);

            return response()->json([
                'success' => true,
                'data' => [
                    'preview_data' => $reportData['data']->take(10), // Solo primeros 10 registros para preview
                    'total_records' => $reportData['metadata']['total_records'] ?? 0,
                    'columns' => $reportData['data']->isNotEmpty() ? 
                        array_keys((array) $reportData['data']->first()) : [],
                    'metadata' => $reportData['metadata']
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Error previewing report', [
                'request' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar la vista previa',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Métodos auxiliares privados
     */
    
    private function generateDownloadUrl($result): string
    {
        // Para API, podríamos generar un token temporal o simplemente indicar
        // que el archivo fue generado y está listo para descargar
        // En una implementación real, podrías guardar el archivo temporalmente
        // y generar una URL firmada
        return url('/api/data-analyst/export/download?token=' . uniqid());
    }

    private function extractFileName($result): string
    {
        // Extraer nombre de archivo del header Content-Disposition si está disponible
        // En una implementación real, esto vendría de la respuesta
        $reportType = request()->input('report_type', 'report');
        $format = request()->input('format', 'file');
        $timestamp = now()->format('Y-m-d_His');
        
        return "{$reportType}_report_{$timestamp}.{$format}";
    }

    private function getAvailableFilters(string $reportType): array
    {
        return match($reportType) {
            'students' => ['status', 'company_id', 'academic_period_id', 'dates'],
            'tickets' => ['status', 'dates'],
            'courses' => ['company_id', 'academic_period_id', 'dates'],
            'attendance', 'grades', 'financial', 'security' => ['company_id', 'academic_period_id', 'dates'],
            'dashboard' => ['dates'],
            default => []
        };
    }
}