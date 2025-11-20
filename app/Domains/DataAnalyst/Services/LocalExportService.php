<?php
// app/Domains/DataAnalyst/Services/LocalExportService.php

namespace App\Domains\DataAnalyst\Services;

use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalExportService
{
    private LocalAnalyticsService $localAnalyticsService;

    public function __construct(LocalAnalyticsService $localAnalyticsService)
    {
        $this->localAnalyticsService = $localAnalyticsService;
    }

    /**
     * Exportar estudiantes activos
     */
    public function exportActiveStudents(array $filters, string $format): string
    {
        $students = $this->localAnalyticsService->getActiveStudents($filters);
        
        $data = [
            'title' => 'Reporte de Estudiantes Activos',
            'filters' => $filters,
            'students' => $students,
            'total_students' => $students->count(),
            'export_date' => now()->format('Y-m-d H:i:s')
        ];

        return $this->generateExport($data, 'active_students', $format);
    }

    /**
     * Exportar grupos con docentes
     */
    public function exportGroupsWithTeachers(array $filters, string $format): string
    {
        $groups = $this->localAnalyticsService->getGroupsWithTeachers($filters);
        
        $data = [
            'title' => 'Reporte de Grupos con Docentes',
            'filters' => $filters,
            'groups' => $groups,
            'total_groups' => $groups->count(),
            'export_date' => now()->format('Y-m-d H:i:s')
        ];

        return $this->generateExport($data, 'groups_teachers', $format);
    }

    /**
     * Exportar grupos con estudiantes
     */
    public function exportGroupsWithStudents(array $filters, string $format): string
    {
        $groups = $this->localAnalyticsService->getGroupsWithStudents($filters);
        
        $data = [
            'title' => 'Reporte de Grupos con Estudiantes',
            'filters' => $filters,
            'groups' => $groups,
            'total_groups' => $groups->unique('group_id')->count(),
            'total_students' => $groups->count(),
            'export_date' => now()->format('Y-m-d H:i:s')
        ];

        return $this->generateExport($data, 'groups_students', $format);
    }

    /**
     * Exportar resumen de asistencia
     */
    public function exportAttendanceSummary(array $filters, string $format): string
    {
        $attendance = $this->localAnalyticsService->getAttendanceSummary($filters);
        
        $data = [
            'title' => 'Reporte de Asistencia',
            'filters' => $filters,
            'attendance' => $attendance,
            'total_records' => $attendance->count(),
            'export_date' => now()->format('Y-m-d H:i:s')
        ];

        return $this->generateExport($data, 'attendance_summary', $format);
    }

    /**
     * Exportar resumen de calificaciones
     */
    public function exportGradesSummary(array $filters, string $format): string
    {
        $grades = $this->localAnalyticsService->getGradesSummary($filters);
        
        $data = [
            'title' => 'Reporte de Calificaciones',
            'filters' => $filters,
            'grades' => $grades,
            'total_records' => $grades->count(),
            'export_date' => now()->format('Y-m-d H:i:s')
        ];

        return $this->generateExport($data, 'grades_summary', $format);
    }

    /**
     * Exportar resumen de pagos
     */
    public function exportPaymentsSummary(array $filters, string $format): string
    {
        $payments = $this->localAnalyticsService->getPaymentsSummary($filters);
        
        $data = [
            'title' => 'Reporte de Pagos',
            'filters' => $filters,
            'payments' => $payments,
            'total_records' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'export_date' => now()->format('Y-m-d H:i:s')
        ];

        return $this->generateExport($data, 'payments_summary', $format);
    }

    /**
     * Exportar tickets de soporte
     */
    public function exportSupportTickets(array $filters, string $format): string
    {
        $tickets = $this->localAnalyticsService->getSupportTickets($filters);
        
        $data = [
            'title' => 'Reporte de Tickets de Soporte',
            'filters' => $filters,
            'tickets' => $tickets,
            'total_records' => $tickets->count(),
            'export_date' => now()->format('Y-m-d H:i:s')
        ];

        return $this->generateExport($data, 'support_tickets', $format);
    }

    /**
     * Exportar citas programadas
     */
    public function exportAppointments(array $filters, string $format): string
    {
        $appointments = $this->localAnalyticsService->getAppointments($filters);
        
        $data = [
            'title' => 'Reporte de Citas Programadas',
            'filters' => $filters,
            'appointments' => $appointments,
            'total_records' => $appointments->count(),
            'export_date' => now()->format('Y-m-d H:i:s')
        ];

        return $this->generateExport($data, 'appointments', $format);
    }

    /**
     * Exportar dashboard rápido
     */
    public function exportQuickDashboard(string $format): string
    {
        $dashboard = $this->localAnalyticsService->getQuickDashboard();
        
        $data = [
            'title' => 'Dashboard Rápido del Sistema',
            'dashboard' => $dashboard,
            'export_date' => now()->format('Y-m-d H:i:s')
        ];

        return $this->generateExport($data, 'quick_dashboard', $format);
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

        $filePath = "exports/local/{$filename}";
        Storage::put($filePath, $pdf->output());
        
        return $filePath;
    }

    /**
     * Generar Excel
     */
    private function generateExcel(array $data, string $type, string $filename): string
    {
        $exportClass = $this->getExportClassForType($type, $data);
        
        $filePath = "exports/local/{$filename}";
        
        Excel::store($exportClass, $filePath);
        
        return $filePath;
    }

    /**
     * Obtener la vista para PDF según el tipo
     */
    private function getViewForType(string $type): string
    {
        return match($type) {
            'active_students' => 'data-analyst.exports.local.active-students-pdf',
            'groups_teachers' => 'data-analyst.exports.local.groups-teachers-pdf',
            'groups_students' => 'data-analyst.exports.local.groups-students-pdf',
            'attendance_summary' => 'data-analyst.exports.local.attendance-summary-pdf',
            'grades_summary' => 'data-analyst.exports.local.grades-summary-pdf',
            'payments_summary' => 'data-analyst.exports.local.payments-summary-pdf',
            'support_tickets' => 'data-analyst.exports.local.support-tickets-pdf',
            'appointments' => 'data-analyst.exports.local.appointments-pdf',
            'quick_dashboard' => 'data-analyst.exports.local.quick-dashboard-pdf',
            default => 'data-analyst.exports.local.default-pdf'
        };
    }

    /**
     * Obtener la clase de exportación para Excel según el tipo
     */
    private function getExportClassForType(string $type, array $data)
    {
        return match($type) {
            'active_students' => new \App\Domains\DataAnalyst\Exports\Local\ActiveStudentsExport($data),
            'groups_teachers' => new \App\Domains\DataAnalyst\Exports\Local\GroupsTeachersExport($data),
            'groups_students' => new \App\Domains\DataAnalyst\Exports\Local\GroupsStudentsExport($data),
            'attendance_summary' => new \App\Domains\DataAnalyst\Exports\Local\AttendanceSummaryExport($data),
            'grades_summary' => new \App\Domains\DataAnalyst\Exports\Local\GradesSummaryExport($data),
            'payments_summary' => new \App\Domains\DataAnalyst\Exports\Local\PaymentsSummaryExport($data),
            'support_tickets' => new \App\Domains\DataAnalyst\Exports\Local\SupportTicketsExport($data),
            'quick_dashboard' => new \App\Domains\DataAnalyst\Exports\Local\QuickDashboardExport($data),
            default => new \App\Domains\DataAnalyst\Exports\Local\DefaultExport($data)
        };
    }

    /**
     * Generar nombre de archivo único
     */
    private function generateFilename(string $type, string $format): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $extension = $format === 'pdf' ? 'pdf' : 'xlsx';
        
        return "{$type}_{$timestamp}.{$extension}";
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
     * Limpiar archivos antiguos
     */
    public function cleanupOldExports(int $hours = 24): void
    {
        $files = Storage::files('exports/local');
        $cutoffTime = now()->subHours($hours);
        
        foreach ($files as $file) {
            if (Storage::lastModified($file) < $cutoffTime->timestamp) {
                Storage::delete($file);
            }
        }
    }
}