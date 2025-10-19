<?php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Domains\DataAnalyst\Services\CourseReportService;
use App\Domains\DataAnalyst\Http\Requests\CourseReportRequest;
use App\Domains\Lms\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CourseReportController
{
    protected $courseReportService;

    public function __construct(CourseReportService $courseReportService)
    {
        $this->courseReportService = $courseReportService;
    }

    /**
     * Mostrar listado general de cursos con filtros
     */
    public function index(CourseReportRequest $request): View
    {
        $courses = $this->courseReportService->getCourseReport($request->validated());
        $categories = Category::all(); // Obtener categorías para el filtro
        
        return view('dataanalyst.courses.index', compact('courses', 'categories'));
    }

    /**
     * Mostrar detalle de un curso específico
     */
    public function show($courseId): View
    {
        $course = $this->courseReportService->getCourseDetail($courseId);
        
        return view('dataanalyst.courses.show', compact('course'));
    }

    /**
     * Obtener estadísticas de cursos (API)
     */
    public function statistics(CourseReportRequest $request): JsonResponse
    {
        $statistics = $this->courseReportService->getCourseStatistics($request->validated());
        
        return response()->json($statistics);
    }
}