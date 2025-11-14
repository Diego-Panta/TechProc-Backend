<?php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Domains\DataAnalyst\Services\ExportReportService;
use App\Domains\DataAnalyst\Http\Requests\Api\ExportReportRequest;
use App\Domains\DataAnalyst\Models\ExportedReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportReportApiController
{
    public function __construct(
        private ExportReportService $exportReportService
    ) {}
    
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

    public function generateReport(ExportReportRequest $request): JsonResponse
    {
        try {
            $result = $this->exportReportService->generateAndSaveReport($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Reporte generado y guardado exitosamente',
                'data' => [
                    'report' => $this->formatReportResponse($result['report']),
                    'download_url' => $result['download_url']
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Error generating report', [
                'request' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Listar reportes del usuario
     */
    public function listReports(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['report_type', 'format', 'search']);
            $perPage = $request->get('per_page', 15);
            $userId = $this->getAuthenticatedUserId();

            $reports = $this->exportReportService->getUserReports($userId, $filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'reports' => $reports->map(function ($report) {
                        return $this->formatReportResponse($report);
                    }),
                    'pagination' => [
                        'current_page' => $reports->currentPage(),
                        'last_page' => $reports->lastPage(),
                        'per_page' => $reports->perPage(),
                        'total' => $reports->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing reports', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de reportes'
            ], 500);
        }
    }

    /**
     * Descargar reporte por token
     */
    public function downloadReport(string $token): StreamedResponse|JsonResponse
    {
        try {
            $report = $this->exportReportService->getReportByToken($token);

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reporte no encontrado o expirado'
                ], 404);
            }

            // Verificar que el archivo existe
            if (!$report->fileExists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo del reporte no existe'
                ], 404);
            }

            // Verificar que no esté expirado
            if ($report->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El reporte ha expirado'
                ], 410);
            }

            // Obtener el storage path y MIME type
            $filePath = $report->file_path;
            $fileName = $report->file_name;
            
            // Determinar el MIME type basado en la extensión
            $mimeType = $this->getMimeType($report->format);

            Log::info("Descargando reporte", [
                'token' => $token,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'mime_type' => $mimeType
            ]);

            // Crear respuesta de stream con headers explícitos
            return Storage::download($filePath, $fileName, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);

        } catch (\Exception $e) {
            Log::error('API Error downloading report', [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al descargar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener MIME type basado en el formato
     */
    private function getMimeType(string $format): string
    {
        return match($format) {
            'pdf' => 'application/pdf',
            'excel' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'application/octet-stream'
        };
    }

    /**
     * Obtener estadísticas de reportes
     */
    public function getStats(): JsonResponse
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $stats = $this->exportReportService->getReportStats($userId);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting report stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas'
            ], 500);
        }
    }

    /**
     * Eliminar reporte
     */
    public function deleteReport(string $token): JsonResponse
    {
        try {
            $report = $this->exportReportService->getReportByToken($token);

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reporte no encontrado'
                ], 404);
            }

            // Verificar que el usuario es el propietario del reporte
            if ($report->generated_by !== $this->getAuthenticatedUserId()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar este reporte'
                ], 403);
            }

            $this->exportReportService->deleteReport($report);

            return response()->json([
                'success' => true,
                'message' => 'Reporte eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('API Error deleting report', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el reporte'
            ], 500);
        }
    }

    /**
     * Formatear respuesta del reporte para API
     */
    private function formatReportResponse(ExportedReport $report): array
    {
        return [
            'id' => $report->id,
            'report_type' => $report->report_type,
            'report_type_name' => $report->report_type_name,
            'format' => $report->format,
            'file_name' => $report->file_name,
            'report_title' => $report->report_title,
            'description' => $report->description,
            'file_size' => $report->formatted_file_size,
            'record_count' => $report->record_count,
            'filters' => $report->filters,
            'generated_by' => [
                'id' => $report->generated_by,
                'name' => $report->generatedBy->name ?? 'Usuario del Sistema'
            ],
            'download_url' => $report->getDownloadUrl(),
            'created_at' => $report->created_at->toISOString(),
            'expires_at' => $report->expires_at?->toISOString(),
            'is_expired' => $report->isExpired(),
            'icon' => $report->report_icon
        ];
    }

    /**
     * Obtener ID del usuario autenticado de forma segura
     */
    private function getAuthenticatedUserId(): int
    {
        // Primero intentar obtener el usuario del request (viene del middleware)
        if (request()->has('authenticated_user')) {
            $user = request()->get('authenticated_user');
            if ($user && isset($user->id)) {
                return $user->id;
            }
        }

        // Si no está en el request, intentar con Auth
        if (Auth::check()) {
            return Auth::id();
        }

        // Si no hay usuario autenticado, usar usuario por defecto
        $defaultUser = \App\Models\User::first();
        return $defaultUser ? $defaultUser->id : 1;
    }
}