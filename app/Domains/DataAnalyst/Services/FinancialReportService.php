<?php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Repositories\FinancialReportRepository;
use App\Domains\DataAnalyst\Models\Invoice;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    protected $financialReportRepository;

    public function __construct(FinancialReportRepository $financialReportRepository)
    {
        $this->financialReportRepository = $financialReportRepository;
    }

    public function getFinancialReport(array $filters = [])
    {
        return $this->financialReportRepository->getFinancialReport($filters);
    }

    public function getFinancialStatistics(array $filters = [])
    {
        return $this->financialReportRepository->getFinancialStatistics($filters);
    }

    public function getRevenueTrend(array $filters = [])
    {
        return $this->financialReportRepository->getRevenueTrend($filters);
    }

    /**
     * Obtener fuentes de ingresos para el filtro
     */
    public function getRevenueSources()
    {
        return $this->financialReportRepository->getRevenueSources();
    }

    /**
     * Obtener detalles de pagos pendientes - CORREGIDO PARA MySQL
     */
    public function getPendingPayments(array $filters = [])
    {
        $perPage = $filters['per_page'] ?? 20;
        
        $query = Invoice::with(['revenueSource', 'enrollment'])
            ->where('status', 'Pending')
            ->select('invoices.*')
            ->addSelect(DB::raw("DATEDIFF(CURDATE(), invoices.issue_date) as days_overdue"));

        // Aplicar filtros
        if (!empty($filters['start_date'])) {
            $query->whereDate('issue_date', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->whereDate('issue_date', '<=', $filters['end_date']);
        }
        
        if (!empty($filters['revenue_source_id'])) {
            $query->where('revenue_source_id', $filters['revenue_source_id']);
        }
        
        if (!empty($filters['min_amount'])) {
            $query->where('total_amount', '>=', $filters['min_amount']);
        }
        
        if (!empty($filters['max_amount'])) {
            $query->where('total_amount', '<=', $filters['max_amount']);
        }

        $invoices = $query->orderBy('issue_date', 'asc')
                         ->paginate($perPage);

        $summary = [
            'count' => $invoices->total(),
            'total_amount' => $invoices->sum('total_amount')
        ];

        return [
            'summary' => $summary,
            'invoices' => $invoices->items(),
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
                'last_page' => $invoices->lastPage()
            ]
        ];
    }
}