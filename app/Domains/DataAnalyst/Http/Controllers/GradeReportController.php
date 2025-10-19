<?php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Domains\DataAnalyst\Services\GradeReportService;
use App\Domains\DataAnalyst\Http\Requests\GradeReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class GradeReportController
{
    protected $gradeReportService;

    public function __construct(GradeReportService $gradeReportService)
    {
        $this->gradeReportService = $gradeReportService;
    }

    /**
     * Mostrar reporte general de calificaciones con filtros
     */
    public function index(GradeReportRequest $request): View
    {
        $gradesReport = $this->gradeReportService->getGradeReport($request->validated());
        $filterData = $this->gradeReportService->getFilterData($request->validated());
        
        return view('dataanalyst.grades.index', compact('gradesReport', 'filterData'));
    }

    /**
     * Obtener estadísticas de calificaciones (API)
     */
    public function statistics(GradeReportRequest $request): JsonResponse
    {
        $statistics = $this->gradeReportService->getGradeStatistics($request->validated());
        
        return response()->json($statistics);
    }

    /**
     * Obtener estudiantes con mejor rendimiento
     */
    public function topPerformers(GradeReportRequest $request): JsonResponse
    {
        $topPerformers = $this->gradeReportService->getTopPerformers($request->validated());
        
        return response()->json($topPerformers);
    }

    public function getGroupsByCourse(GradeReportRequest $request): JsonResponse
    {
        $groups = $this->gradeReportService->getFilterData(['course_id' => $request->course_id])['groups'];
        
        return response()->json($groups);
    }
}