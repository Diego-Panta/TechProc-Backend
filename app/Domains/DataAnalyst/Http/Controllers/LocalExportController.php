<?php
// app/Domains/DataAnalyst/Http/Controllers/LocalExportController.php

namespace App\Domains\DataAnalyst\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Domains\DataAnalyst\Services\LocalExportService;
use Illuminate\Support\Facades\Log;

class LocalExportController extends Controller
{
    protected LocalExportService $exportService;

    public function __construct(LocalExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Exportar estudiantes activos
     */
    public function exportActiveStudents(Request $request)
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_id' => 'sometimes|integer',
                'payment_status' => 'sometimes|string',
                'format' => 'required|in:pdf,excel'
            ]);

            $format = $filters['format'];
            unset($filters['format']);

            $filePath = $this->exportService->exportActiveStudents($filters, $format);

            return $this->exportService->downloadFile($filePath);
        } catch (\Exception $e) {
            Log::error('Error en exportActiveStudents: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error exportando estudiantes activos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar grupos con docentes
     */
    public function exportGroupsWithTeachers(Request $request)
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_id' => 'sometimes|integer',
                'status' => 'sometimes|string',
                'format' => 'required|in:pdf,excel'
            ]);

            $format = $filters['format'];
            unset($filters['format']);

            $filePath = $this->exportService->exportGroupsWithTeachers($filters, $format);

            return $this->exportService->downloadFile($filePath);
        } catch (\Exception $e) {
            Log::error('Error en exportGroupsWithTeachers: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error exportando grupos con docentes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar grupos con estudiantes
     */
    public function exportGroupsWithStudents(Request $request)
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'academic_status' => 'sometimes|string',
                'payment_status' => 'sometimes|string',
                'format' => 'required|in:pdf,excel'
            ]);

            $format = $filters['format'];
            unset($filters['format']);

            $filePath = $this->exportService->exportGroupsWithStudents($filters, $format);

            return $this->exportService->downloadFile($filePath);
        } catch (\Exception $e) {
            Log::error('Error en exportGroupsWithStudents: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error exportando grupos con estudiantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar resumen de asistencia
     */
    public function exportAttendanceSummary(Request $request)
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'student_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date',
                'format' => 'required|in:pdf,excel'
            ]);

            $format = $filters['format'];
            unset($filters['format']);

            $filePath = $this->exportService->exportAttendanceSummary($filters, $format);

            return $this->exportService->downloadFile($filePath);
        } catch (\Exception $e) {
            Log::error('Error en exportAttendanceSummary: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error exportando resumen de asistencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar resumen de calificaciones
     */
    public function exportGradesSummary(Request $request)
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'student_id' => 'sometimes|integer',
                'module_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date',
                'format' => 'required|in:pdf,excel'
            ]);

            $format = $filters['format'];
            unset($filters['format']);

            $filePath = $this->exportService->exportGradesSummary($filters, $format);

            return $this->exportService->downloadFile($filePath);
        } catch (\Exception $e) {
            Log::error('Error en exportGradesSummary: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error exportando resumen de calificaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar dashboard rápido
     */
    public function exportQuickDashboard(Request $request)
    {
        try {
            $request->validate([
                'format' => 'required|in:pdf,excel'
            ]);

            $format = $request->format;

            $filePath = $this->exportService->exportQuickDashboard($format);

            return $this->exportService->downloadFile($filePath);
        } catch (\Exception $e) {
            Log::error('Error en exportQuickDashboard: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error exportando dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar resumen de pagos
     */
    public function exportPaymentsSummary(Request $request)
    {
        try {
            $filters = $request->validate([
                'status' => 'sometimes|string',
                'group_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date',
                'format' => 'required|in:pdf,excel'
            ]);

            $format = $filters['format'];
            unset($filters['format']);

            $filePath = $this->exportService->exportPaymentsSummary($filters, $format);

            return $this->exportService->downloadFile($filePath);
        } catch (\Exception $e) {
            Log::error('Error en exportPaymentsSummary: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error exportando resumen de pagos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar tickets de soporte
     */
    public function exportSupportTickets(Request $request)
    {
        try {
            $filters = $request->validate([
                'status' => 'sometimes|string',
                'priority' => 'sometimes|string',
                'type' => 'sometimes|string',
                'group_id' => 'sometimes|integer',
                'format' => 'required|in:pdf,excel'
            ]);

            $format = $filters['format'];
            unset($filters['format']);

            $filePath = $this->exportService->exportSupportTickets($filters, $format);

            return $this->exportService->downloadFile($filePath);
        } catch (\Exception $e) {
            Log::error('Error en exportSupportTickets: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error exportando tickets de soporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar citas programadas
     */
    public function exportAppointments(Request $request)
    {
        try {
            $filters = $request->validate([
                'teacher_id' => 'sometimes|integer',
                'student_id' => 'sometimes|integer',
                'status' => 'sometimes|string',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date',
                'format' => 'required|in:pdf,excel'
            ]);

            $format = $filters['format'];
            unset($filters['format']);

            $filePath = $this->exportService->exportAppointments($filters, $format);

            return $this->exportService->downloadFile($filePath);
        } catch (\Exception $e) {
            Log::error('Error en exportAppointments: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error exportando citas programadas: ' . $e->getMessage()
            ], 500);
        }
    }
}
