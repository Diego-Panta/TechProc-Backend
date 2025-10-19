<?php

namespace App\Domains\DataAnalyst\Http\Controllers;

use App\Domains\DataAnalyst\Services\TicketReportService;
use App\Domains\DataAnalyst\Http\Requests\TicketReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TicketReportController
{
    protected $ticketReportService;

    public function __construct(TicketReportService $ticketReportService)
    {
        $this->ticketReportService = $ticketReportService;
    }

    /**
     * Mostrar listado de tickets con filtros
     */
    public function index(TicketReportRequest $request): View
    {
        $tickets = $this->ticketReportService->getTicketReport($request->validated());
        
        return view('dataanalyst.tickets.index', compact('tickets'));
    }

    /**
     * Obtener estadísticas de tickets (API)
     */
    public function statistics(TicketReportRequest $request): JsonResponse
    {
        $statistics = $this->ticketReportService->getTicketStatistics($request->validated());
        
        return response()->json($statistics);
    }
}