<?php

namespace App\Domains\DataAnalyst\Repositories;

use App\Domains\DataAnalyst\Models\ExportedReport;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ExportedReportRepository
{
    /**
     * Guardar registro de reporte exportado
     */
    public function saveReport(array $data): ExportedReport
    {
        return ExportedReport::create($data);
    }

    /**
     * Obtener reporte por token de acceso
     */
    public function getReportByToken(string $token): ?ExportedReport
    {
        return ExportedReport::where('access_token', $token)
            ->notExpired()
            ->first();
    }

    /**
     * Obtener reportes recientes del usuario
     */
    public function getRecentUserReports(int $userId, int $limit = 10): Collection
    {
        return ExportedReport::with('generatedBy')
            ->byUser($userId)
            ->notExpired()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Listar todos los reportes con paginación
     */
    public function listReports(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ExportedReport::with('generatedBy')
            ->notExpired()
            ->orderBy('created_at', 'desc');

        // Aplicar filtros
        if (!empty($filters['report_type'])) {
            $query->byType($filters['report_type']);
        }

        if (!empty($filters['format'])) {
            $query->byFormat($filters['format']);
        }

        if (!empty($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('report_title', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('description', 'ILIKE', "%{$filters['search']}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Obtener estadísticas de reportes
     */
    public function getReportStats(int $userId = null): array
    {
        $query = ExportedReport::notExpired();

        if ($userId) {
            $query->byUser($userId);
        }

        $totalReports = $query->count();
        $totalSize = $query->sum('file_size');
        $reportsByType = $query->selectRaw('report_type, COUNT(*) as count')
            ->groupBy('report_type')
            ->get()
            ->pluck('count', 'report_type')
            ->toArray();

        $reportsByFormat = $query->selectRaw('format, COUNT(*) as count')
            ->groupBy('format')
            ->get()
            ->pluck('count', 'format')
            ->toArray();

        return [
            'total_reports' => $totalReports,
            'total_size' => $totalSize,
            'reports_by_type' => $reportsByType,
            'reports_by_format' => $reportsByFormat,
            'formatted_total_size' => $this->formatFileSize($totalSize),
        ];
    }

    /**
     * Eliminar reporte y su archivo
     */
    public function deleteReport(ExportedReport $report): bool
    {
        // Eliminar archivo físico
        if ($report->fileExists()) {
            Storage::delete($report->file_path);
        }

        // Eliminar registro
        return $report->delete();
    }

    /**
     * Formatear tamaño de archivo
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}