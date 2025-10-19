<?php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Domains\DataAnalyst\Services\SecurityReportService;
use App\Domains\DataAnalyst\Http\Requests\SecurityReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SecurityReportController
{
    protected $securityReportService;

    public function __construct(SecurityReportService $securityReportService)
    {
        $this->securityReportService = $securityReportService;
    }

    /**
     * Mostrar reporte general de seguridad
     */
    public function index(SecurityReportRequest $request): View
    {
        $securityData = $this->securityReportService->getSecurityReport($request->validated());

        return view('dataanalyst.security.index', compact('securityData'));
    }

    /**
     * Obtener análisis de seguridad (API)
     */
    public function analysis(SecurityReportRequest $request): JsonResponse
    {
        $analysis = $this->securityReportService->getSecurityAnalysis($request->validated());

        return response()->json($analysis);
    }

    /**
     * Obtener detalles de eventos de seguridad
     */
    public function events(SecurityReportRequest $request): JsonResponse
    {
        $events = $this->securityReportService->getSecurityEvents($request->validated());

        return response()->json($events);
    }

    public function alerts(SecurityReportRequest $request): JsonResponse
    {
        $alerts = $this->securityReportService->getSecurityAlerts($request->validated());

        return response()->json($alerts);
    }
}
