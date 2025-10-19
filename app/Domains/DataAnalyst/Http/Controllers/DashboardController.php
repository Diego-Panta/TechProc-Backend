<?php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Domains\DataAnalyst\Services\DashboardService;
use App\Domains\DataAnalyst\Http\Requests\DashboardRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Mostrar dashboard general de analítica
     */
    public function index(DashboardRequest $request): View
    {
        $dashboardData = $this->dashboardService->getDashboardData($request->validated());
        
        return view('dataanalyst.dashboard.index', compact('dashboardData'));
    }

    /**
     * Obtener datos del dashboard (API)
     */
    public function data(DashboardRequest $request): JsonResponse
    {
        $dashboardData = $this->dashboardService->getDashboardData($request->validated());
        
        return response()->json($dashboardData);
    }
}