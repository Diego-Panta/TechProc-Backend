<?php
// app/Domains/DataAnalyst/Http/Controllers/LocalAnalyticsController.php

namespace App\Domains\DataAnalyst\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Domains\DataAnalyst\Services\LocalAnalyticsService;

class LocalAnalyticsController extends Controller
{
    protected LocalAnalyticsService $localService;

    public function __construct(LocalAnalyticsService $localService)
    {
        $this->localService = $localService;
    }

    /**
     * Estudiantes activos
     */
    public function getActiveStudents(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_id' => 'sometimes|integer',
                'payment_status' => 'sometimes|string'
            ]);

            $students = $this->localService->getActiveStudents($filters);

            return response()->json([
                'success' => true,
                'data' => $students,
                'count' => $students->count(),
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo estudiantes activos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Grupos con docentes
     */
    public function getGroupsWithTeachers(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'course_id' => 'sometimes|integer',
                'status' => 'sometimes|string'
            ]);

            $groups = $this->localService->getGroupsWithTeachers($filters);

            return response()->json([
                'success' => true,
                'data' => $groups,
                'count' => $groups->count(),
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo grupos con docentes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Grupos con estudiantes
     */
    public function getGroupsWithStudents(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'academic_status' => 'sometimes|string',
                'payment_status' => 'sometimes|string'
            ]);

            $groups = $this->localService->getGroupsWithStudents($filters);

            return response()->json([
                'success' => true,
                'data' => $groups,
                'count' => $groups->count(),
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo grupos con estudiantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resumen de asistencia
     */
    public function getAttendanceSummary(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'student_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date'
            ]);

            $attendance = $this->localService->getAttendanceSummary($filters);

            return response()->json([
                'success' => true,
                'data' => $attendance,
                'count' => $attendance->count(),
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo resumen de asistencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resumen de calificaciones
     */
    public function getGradesSummary(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer',
                'student_id' => 'sometimes|integer',
                'module_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date'
            ]);

            $grades = $this->localService->getGradesSummary($filters);

            return response()->json([
                'success' => true,
                'data' => $grades,
                'count' => $grades->count(),
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo resumen de calificaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resumen de pagos
     */
    public function getPaymentsSummary(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'status' => 'sometimes|string',
                'group_id' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date'
            ]);

            $payments = $this->localService->getPaymentsSummary($filters);

            return response()->json([
                'success' => true,
                'data' => $payments,
                'count' => $payments->count(),
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo resumen de pagos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tickets de soporte
     */
    public function getSupportTickets(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'status' => 'sometimes|string',
                'priority' => 'sometimes|string',
                'type' => 'sometimes|string',
                'group_id' => 'sometimes|integer'
            ]);

            $tickets = $this->localService->getSupportTickets($filters);

            return response()->json([
                'success' => true,
                'data' => $tickets,
                'count' => $tickets->count(),
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo tickets de soporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Citas programadas
     */
    public function getAppointments(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'teacher_id' => 'sometimes|integer',
                'student_id' => 'sometimes|integer',
                'status' => 'sometimes|string',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date'
            ]);

            $appointments = $this->localService->getAppointments($filters);

            return response()->json([
                'success' => true,
                'data' => $appointments,
                'count' => $appointments->count(),
                'filters' => $filters
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo citas programadas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dashboard rápido
     */
    public function getQuickDashboard(): JsonResponse
    {
        try {
            $dashboard = $this->localService->getQuickDashboard();

            return response()->json([
                'success' => true,
                'data' => $dashboard
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte combinado
     */
    public function getCombinedReport(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'group_id' => 'sometimes|integer'
            ]);

            $report = $this->localService->getCombinedReport($filters);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo reporte combinado: ' . $e->getMessage()
            ], 500);
        }
    }
}