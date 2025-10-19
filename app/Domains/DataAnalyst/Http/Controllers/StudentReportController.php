<?php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Domains\DataAnalyst\Services\StudentReportService;
use App\Domains\DataAnalyst\Http\Requests\StudentReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class StudentReportController
{
    protected $studentReportService;

    public function __construct(StudentReportService $studentReportService)
    {
        $this->studentReportService = $studentReportService;
    }

    /**
     * Mostrar listado general de estudiantes con filtros
     */
    public function index(StudentReportRequest $request): View
    {
        $students = $this->studentReportService->getStudentReport($request->validated());
        
        return view('dataanalyst.students.index', compact('students'));
    }

    /**
     * Mostrar detalle de un estudiante específico
     */
    public function show($studentId): View
    {
        $student = $this->studentReportService->getStudentDetail($studentId);
        
        return view('dataanalyst.students.show', compact('student'));
    }

    /**
     * Obtener estadísticas de estudiantes (API)
     */
    public function statistics(StudentReportRequest $request): JsonResponse
    {
        $statistics = $this->studentReportService->getStudentStatistics($request->validated());
        
        return response()->json($statistics);
    }
}