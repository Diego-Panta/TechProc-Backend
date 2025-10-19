<?php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Domains\DataAnalyst\Services\AttendanceReportService;
use App\Domains\DataAnalyst\Http\Requests\AttendanceReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AttendanceReportController
{
    protected $attendanceReportService;

    public function __construct(AttendanceReportService $attendanceReportService)
    {
        $this->attendanceReportService = $attendanceReportService;
    }

    /**
     * Mostrar reporte de asistencia con filtros
     */
    public function index(AttendanceReportRequest $request): View
    {
        $attendanceData = $this->attendanceReportService->getAttendanceReport($request->validated());
        $courses = $this->attendanceReportService->getCoursesForFilter();
        $students = $this->attendanceReportService->getStudentsForFilter();
        
        return view('dataanalyst.attendance.index', compact('attendanceData', 'courses', 'students'));
    }

    /**
     * Obtener estadísticas de asistencia (API)
     */
    public function statistics(AttendanceReportRequest $request): JsonResponse
    {
        $statistics = $this->attendanceReportService->getAttendanceStatistics($request->validated());
        
        return response()->json($statistics);
    }

    /**
     * Obtener tendencia de asistencia (API)
     */
    public function trend(AttendanceReportRequest $request): JsonResponse
    {
        $trend = $this->attendanceReportService->getAttendanceTrend($request->validated());
        
        return response()->json($trend);
    }
}