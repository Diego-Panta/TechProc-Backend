<?php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Domains\DataAnalyst\Services\FinancialReportService;
use App\Domains\DataAnalyst\Http\Requests\FinancialReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class FinancialReportController
{
    protected $financialReportService;

    public function __construct(FinancialReportService $financialReportService)
    {
        $this->financialReportService = $financialReportService;
    }

    /**
     * Mostrar dashboard financiero con filtros
     */
    public function index(FinancialReportRequest $request): View
    {
        $financialData = $this->financialReportService->getFinancialReport($request->validated());
        $revenueSources = $this->financialReportService->getRevenueSources();
        
        return view('dataanalyst.financial.index', compact('financialData', 'revenueSources'));
    }

    /**
     * Obtener estadísticas financieras (API)
     */
    public function statistics(FinancialReportRequest $request): JsonResponse
    {
        $statistics = $this->financialReportService->getFinancialStatistics($request->validated());
        
        return response()->json($statistics);
    }

    /**
     * Obtener tendencias de ingresos (API)
     */
    public function revenueTrend(FinancialReportRequest $request): JsonResponse
    {
        $trends = $this->financialReportService->getRevenueTrend($request->validated());
        
        return response()->json($trends);
    }

    /**
     * Obtener fuentes de ingresos (API)
     */
    public function revenueSources(): JsonResponse
    {
        $sources = $this->financialReportService->getRevenueSources();
        
        return response()->json($sources);
    }
}