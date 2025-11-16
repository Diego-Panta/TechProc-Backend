<?php
// app/Domains/DataAnalyst/Services/ExportService.php

namespace App\Domains\DataAnalyst\Services;

use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportService
{
    private BigQueryAnalyticsService $analyticsService;
    private DropoutPredictionService $predictionService;

    public function __construct(
        BigQueryAnalyticsService $analyticsService,
        DropoutPredictionService $predictionService
    ) {
        $this->analyticsService = $analyticsService;
        $this->predictionService = $predictionService;
    }

    /**
     * Exportar métricas de asistencia
     */
    public function exportAttendance(array $filters, string $format): string
    {
        $metrics = $this->analyticsService->getAttendanceMetrics($filters);

        $data = [
            'title' => 'Reporte de Asistencia',
            'filters' => $filters,
            'summary' => $metrics['summary'] ?? [],
            'student_data' => $metrics['student_level'] ?? [],
            'group_data' => $metrics['group_level'] ?? [],
            'export_date' => now()->format('Y-m-d H:i:s')
        ];

        return $this->generateExport($data, 'attendance', $format);
    }

    /**
     * Exportar métricas de progreso
     */
    public function exportProgress(array $filters, string $format): string
    {
        $metrics = $this->analyticsService->getProgressMetrics($filters);

        $data = [
            'title' => 'Reporte de Progreso',
            'filters' => $filters,
            'summary' => $metrics['summary'] ?? [],
            'module_data' => $metrics['module_completion'] ?? [],
            'grade_data' => $metrics['grade_consistency'] ?? [],
            'export_date' => now()->format('Y-m-d H:i:s')
        ];

        return $this->generateExport($data, 'progress', $format);
    }

    /**
     * Exportar métricas de rendimiento
     */
    public function exportPerformance(array $filters, string $format): string
    {
        $metrics = $this->analyticsService->getPerformanceMetrics($filters);

        $data = [
            'title' => 'Reporte de Rendimiento',
            'filters' => $filters,
            'summary' => $metrics['summary'] ?? [],
            'student_performance' => $metrics['student_performance'] ?? [],
            'course_performance' => $metrics['course_performance'] ?? [],
            'export_date' => now()->format('Y-m-d H:i:s')
        ];

        return $this->generateExport($data, 'performance', $format);
    }

    /**
     * Exportar predicciones de deserción
     */
    public function exportDropoutPredictions(array $filters, string $format): string
    {
        $result = $this->predictionService->getDropoutPredictions($filters);
        
        $data = [
            'title' => 'Reporte de Predicciones de Deserción',
            'filters' => $filters,
            'summary' => $result['summary'] ?? [],
            'predictions' => $result['predictions'] ?? [],
            'export_date' => now()->format('Y-m-d H:i:s')
        ];

        return $this->generateExport($data, 'dropout_predictions', $format);
    }

    /**
     * Exportar predicciones detalladas de deserción
     */
    public function exportDetailedDropoutPredictions(array $filters, string $format): string
    {
        $result = $this->predictionService->getDetailedPredictions($filters);
        
        $data = [
            'title' => 'Reporte Detallado de Predicciones de Deserción',
            'filters' => $filters,
            'students' => $result['students'] ?? [],
            'analysis' => $result['analysis'] ?? [],
            'export_date' => now()->format('Y-m-d H:i:s')
        ];

        return $this->generateExport($data, 'detailed_dropout_predictions', $format);
    }

    /**
     * Exportar estudiantes de alto riesgo
     */
    public function exportHighRiskStudents(array $filters, string $format): string
    {
        $highRiskStudents = $this->predictionService->getHighRiskStudents();
        
        $data = [
            'title' => 'Estudiantes de Alto Riesgo - Intervención Inmediata',
            'filters' => $filters,
            'high_risk_students' => $highRiskStudents,
            'export_date' => now()->format('Y-m-d H:i:s')
        ];

        return $this->generateExport($data, 'high_risk_students', $format);
    }

    /**
     * Generar el archivo de exportación
     */
    private function generateExport(array $data, string $type, string $format): string
    {
        $filename = $this->generateFilename($type, $format);

        if ($format === 'pdf') {
            return $this->generatePDF($data, $type, $filename);
        } else {
            return $this->generateExcel($data, $type, $filename);
        }
    }

    /**
     * Generar PDF
     */
    private function generatePDF(array $data, string $type, string $filename): string
    {
        $view = $this->getViewForType($type);

        $pdf = PDF::loadView($view, $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true
            ]);

        $filePath = "exports/{$filename}";
        Storage::put($filePath, $pdf->output());

        return $filePath;
    }

    /**
     * Generar Excel
     */
    private function generateExcel(array $data, string $type, string $filename): string
    {
        $exportClass = $this->getExportClassForType($type, $data);

        $filePath = "exports/{$filename}";

        Excel::store($exportClass, $filePath);

        return $filePath;
    }

    /**
     * Obtener la vista para PDF según el tipo
     */
    private function getViewForType(string $type): string
    {
        return match ($type) {
            'attendance' => 'data-analyst.exports.attendance-pdf',
            'progress' => 'data-analyst.exports.progress-pdf',
            'performance' => 'data-analyst.exports.performance-pdf',
            'dropout_predictions' => 'data-analyst.exports.dropout-predictions-pdf',
            'detailed_dropout_predictions' => 'data-analyst.exports.detailed-dropout-predictions-pdf',
            'high_risk_students' => 'data-analyst.exports.high-risk-students-pdf',
            default => 'data-analyst.exports.default-pdf'
        };
    }

    /**
     * Obtener la clase de exportación para Excel según el tipo
     */
    private function getExportClassForType(string $type, array $data)
    {
        return match ($type) {
            'attendance' => new \App\Domains\DataAnalyst\Exports\AttendanceExport($data),
            'progress' => new \App\Domains\DataAnalyst\Exports\ProgressExport($data),
            'performance' => new \App\Domains\DataAnalyst\Exports\PerformanceExport($data),
            'dropout_predictions' => new \App\Domains\DataAnalyst\Exports\DropoutPredictionsExport($data),
            'detailed_dropout_predictions' => new \App\Domains\DataAnalyst\Exports\DetailedDropoutPredictionsExport($data),
            'high_risk_students' => new \App\Domains\DataAnalyst\Exports\HighRiskStudentsExport($data),
            default => new \App\Domains\DataAnalyst\Exports\DefaultExport($data),
        };
    }

    /**
     * Generar nombre de archivo único
     */
    private function generateFilename(string $type, string $format): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $extension = $format === 'pdf' ? 'pdf' : 'xlsx';

        return "{$type}_report_{$timestamp}.{$extension}";
    }

    /**
     * Descargar archivo exportado
     */
    public function downloadFile(string $filePath)
    {
        if (!Storage::exists($filePath)) {
            throw new \Exception("Archivo no encontrado: {$filePath}");
        }

        return Storage::download($filePath);
    }

    /**
     * Limpiar archivos antiguos (opcional, para mantenimiento)
     */
    public function cleanupOldExports(int $hours = 24): void
    {
        $files = Storage::files('exports');
        $cutoffTime = now()->subHours($hours);

        foreach ($files as $file) {
            if (Storage::lastModified($file) < $cutoffTime->timestamp) {
                Storage::delete($file);
            }
        }
    }
}
