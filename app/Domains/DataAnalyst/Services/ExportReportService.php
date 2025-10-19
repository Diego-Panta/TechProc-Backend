<?php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Repositories\ExportReportRepository;
use App\Domains\DataAnalyst\Repositories\ExportedReportRepository;
use App\Domains\DataAnalyst\Models\ReportExport;
use App\Domains\DataAnalyst\Models\ExportedReport;
use App\Domains\Administrator\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Log;

class ExportReportService
{
    protected $exportReportRepository;
    protected $exportedReportRepository;

    public function __construct(
        ExportReportRepository $exportReportRepository,
        ExportedReportRepository $exportedReportRepository
    ) {
        $this->exportReportRepository = $exportReportRepository;
        $this->exportedReportRepository = $exportedReportRepository;
    }

    /**
     * Generar y guardar reporte en la base de datos
     */
    public function generateAndSaveReport(array $data): array
    {
        Log::info("=== INICIANDO GENERACIÓN Y GUARDADO DE REPORTE ===", $data);

        $reportType = $data['report_type'];
        $format = $data['format'];
        $filters = $data['filters'] ?? [];
        $includeCharts = $data['include_charts'] ?? false;
        $includeRawData = $data['include_raw_data'] ?? false;
        $reportTitle = $data['report_title'] ?? $this->generateDefaultTitle($reportType, $filters);

        // Procesar fechas
        if (isset($data['start_date']) && !empty($data['start_date'])) {
            $filters['start_date'] = $data['start_date'];
        }
        if (isset($data['end_date']) && !empty($data['end_date'])) {
            $filters['end_date'] = $data['end_date'];
        }

        try {
            // Validar tipo de reporte
            $validTypes = ['students', 'courses', 'attendance', 'grades', 'financial', 'tickets', 'security', 'dashboard'];
            if (!in_array($reportType, $validTypes)) {
                throw new \Exception("Tipo de reporte no válido: {$reportType}");
            }

            // Obtener datos del reporte
            $reportData = $this->exportReportRepository->getReportData($reportType, $filters);

            // Generar archivo
            if ($format === 'excel') {
                $result = $this->generateExcelFile($reportData, $reportTitle, $reportType);
            } else {
                $result = $this->generatePdfFile($reportData, $reportTitle, $includeCharts, $includeRawData);
            }

            // Obtener ID de usuario de forma segura
            $userId = $this->getAuthenticatedUserId();

            // Guardar en base de datos
            $savedReport = $this->saveReportToDatabase([
                'report_type' => $reportType,
                'format' => $format,
                'file_name' => $result['file_name'],
                'file_path' => $result['file_path'],
                'file_size' => $result['file_size'],
                'report_title' => $reportTitle,
                'filters' => $filters,
                'description' => $this->generateDescription($reportType, $filters),
                'record_count' => $reportData['metadata']['total_records'] ?? 0,
                'generated_by' => $userId,
            ]);

            Log::info("Reporte guardado en base de datos", [
                'report_id' => $savedReport->id,
                'file_path' => $savedReport->file_path,
                'file_size' => $savedReport->formatted_file_size
            ]);

            return [
                'success' => true,
                'report' => $savedReport,
                'download_url' => $savedReport->getDownloadUrl()
            ];

        } catch (\Exception $e) {
            Log::error("ERROR en generateAndSaveReport: " . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Generar archivo Excel y guardar en storage
     */
    private function generateExcelFile($reportData, $title, $reportType): array
    {
        $fileName = $this->generateFileName($reportType, 'xlsx');
        $filePath = "reports/excel/{$fileName}";

        $export = new ReportExport($reportData, $title);

        // Guardar en storage en lugar de descargar directamente
        Excel::store($export, $filePath);

        $fileSize = Storage::size($filePath);

        return [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $fileSize
        ];
    }

    /**
     * Generar archivo PDF y guardar en storage
     */
    private function generatePdfFile($reportData, $title, $includeCharts, $includeRawData): array
    {
        $fileName = $this->generateFileName('report', 'pdf');
        $filePath = "reports/pdf/{$fileName}";

        $generatedBy = $this->getFirstUserName();

        $pdf = Pdf::loadView('dataanalyst.export.templates.pdf', [
            'data' => $reportData,
            'title' => $title,
            'includeCharts' => $includeCharts,
            'includeRawData' => $includeRawData,
            'generatedAt' => Carbon::now(),
            'generatedBy' => $generatedBy
        ]);

        // Guardar en storage
        Storage::put($filePath, $pdf->output());
        $fileSize = Storage::size($filePath);

        return [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $fileSize
        ];
    }

    /**
     * Guardar reporte en base de datos
     */
    private function saveReportToDatabase(array $data): ExportedReport
    {
        $reportData = [
            'report_type' => $data['report_type'],
            'format' => $data['format'],
            'file_name' => $data['file_name'],
            'file_path' => $data['file_path'],
            'report_title' => $data['report_title'],
            'file_size' => $data['file_size'],
            'filters' => $data['filters'],
            'description' => $data['description'],
            'record_count' => $data['record_count'],
            'generated_by' => $data['generated_by'],
            'access_token' => ExportedReport::generateAccessToken(),
            'expires_at' => now()->addDays(30), // Expira en 30 días
        ];

        return $this->exportedReportRepository->saveReport($reportData);
    }

    /**
     * Generar descripción automática del reporte
     */
    private function generateDescription(string $reportType, array $filters): string
    {
        $descriptions = [
            'students' => 'Reporte completo de estudiantes matriculados',
            'courses' => 'Análisis de cursos y su rendimiento',
            'attendance' => 'Reporte de asistencia y participación',
            'grades' => 'Análisis de rendimiento académico',
            'financial' => 'Reporte de ingresos y transacciones',
            'tickets' => 'Análisis de tickets y tiempos de respuesta',
            'security' => 'Reporte de eventos y alertas de seguridad',
            'dashboard' => 'Resumen general del sistema',
        ];

        $description = $descriptions[$reportType] ?? 'Reporte del sistema';

        // Agregar información de filtros
        $filterInfo = [];
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $start = Carbon::parse($filters['start_date'])->format('d/m/Y');
            $end = Carbon::parse($filters['end_date'])->format('d/m/Y');
            $filterInfo[] = "Período: {$start} al {$end}";
        }

        if (!empty($filters['company_id'])) {
            $filterInfo[] = "Empresa ID: {$filters['company_id']}";
        }

        if (!empty($filters['status'])) {
            $filterInfo[] = "Estado: {$filters['status']}";
        }

        if (!empty($filterInfo)) {
            $description .= ' - ' . implode(', ', $filterInfo);
        }

        return $description;
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

    /**
     * Obtener datos para vista previa
     */
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

    /**
     * Obtener reportes del usuario
     */
    public function getUserReports(int $userId, array $filters = [], int $perPage = 15)
    {
        $filters['user_id'] = $userId;
        return $this->exportedReportRepository->listReports($filters, $perPage);
    }

    /**
     * Obtener reporte por token
     */
    public function getReportByToken(string $token): ?ExportedReport
    {
        return $this->exportedReportRepository->getReportByToken($token);
    }

    /**
     * Descargar reporte
     */
    public function downloadReport(ExportedReport $report)
    {
        if (!$report->fileExists()) {
            throw new \Exception('El archivo del reporte no existe');
        }

        if ($report->isExpired()) {
            throw new \Exception('El reporte ha expirado');
        }

        return Storage::download($report->file_path, $report->file_name);
    }

    /**
     * Eliminar reporte
     */
    public function deleteReport(ExportedReport $report): bool
    {
        return $this->exportedReportRepository->deleteReport($report);
    }

    /**
     * Obtener estadísticas de reportes
     */
    public function getReportStats(int $userId = null): array
    {
        return $this->exportedReportRepository->getReportStats($userId);
    }

    /**
     * Obtener ID del usuario autenticado de forma segura
     */
    private function getAuthenticatedUserId(): int
    {
        // Primero intentar obtener el usuario del request (viene del middleware)
        $request = request();
        if ($request->has('authenticated_user')) {
            $user = $request->get('authenticated_user');
            if ($user && isset($user->id)) {
                Log::debug("Usuario obtenido del middleware: {$user->id}");
                return $user->id;
            }
        }

        // Si no está en el request, intentar con Auth
        if (Auth::check()) {
            Log::debug("Usuario obtenido de Auth: " . Auth::id());
            return Auth::id();
        }

        // Si no hay usuario autenticado, usar usuario por defecto
        $defaultUser = User::first();
        Log::warning("No se encontró usuario autenticado, usando usuario por defecto: " . ($defaultUser ? $defaultUser->id : 1));
        return $defaultUser ? $defaultUser->id : 1;
    }

    /**
     * Obtener el nombre del usuario de forma segura
     */
    private function getFirstUserName(): string
    {
        try {
            // Primero intentar obtener el usuario del request (viene del middleware)
            $request = request();
            if ($request->has('authenticated_user')) {
                $user = $request->get('authenticated_user');
                if ($user && isset($user->name) && $user->name) {
                    return (string) $user->name;
                }
            }

            // Si no está en el request, intentar con Auth
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
}