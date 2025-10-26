<?php

namespace App\Domains\DataAnalyst\Repositories;

use App\Domains\DataAnalyst\Models\FinancialTransaction;
use App\Domains\DataAnalyst\Models\Invoice;
use App\Domains\DataAnalyst\Models\Payment;
use App\Domains\DataAnalyst\Models\RevenueSource;
use Illuminate\Support\Facades\DB;

class FinancialReportRepository
{
    public function getFinancialStatistics(array $filters = [])
    {
        // Total de ingresos (transacciones de tipo 'income')
        $revenueQuery = FinancialTransaction::where('transaction_type', 'income');
        
        // Total de gastos (transacciones de tipo 'expense')
        $expenseQuery = FinancialTransaction::where('transaction_type', 'expense');
        
        // Aplicar filtros de fecha
        if (!empty($filters['start_date'])) {
            $revenueQuery->whereDate('transaction_date', '>=', $filters['start_date']);
            $expenseQuery->whereDate('transaction_date', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $revenueQuery->whereDate('transaction_date', '<=', $filters['end_date']);
            $expenseQuery->whereDate('transaction_date', '<=', $filters['end_date']);
        }

        // Aplicar filtro por fuente de ingresos
        if (!empty($filters['revenue_source_id'])) {
            $revenueQuery->whereHas('invoice', function ($q) use ($filters) {
                $q->where('revenue_source_id', $filters['revenue_source_id']);
            });
            
            $expenseQuery->whereHas('invoice', function ($q) use ($filters) {
                $q->where('revenue_source_id', $filters['revenue_source_id']);
            });
        }

        $totalRevenue = $revenueQuery->sum('amount');
        $totalExpenses = $expenseQuery->sum('amount');
        $netIncome = $totalRevenue - $totalExpenses;

        // Ingresos por fuente de ingresos
        $revenueBySource = Invoice::join('revenue_sources', 'invoices.revenue_source_id', '=', 'revenue_sources.id')
            ->selectRaw('revenue_sources.id as source_id, revenue_sources.name as source_name, SUM(invoices.total_amount) as amount')
            ->when(!empty($filters['revenue_source_id']), function ($q) use ($filters) {
                $q->where('revenue_sources.id', $filters['revenue_source_id']);
            })
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('invoices.issue_date', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('invoices.issue_date', '<=', $filters['end_date']);
            })
            ->groupBy('revenue_sources.id', 'revenue_sources.name')
            ->get();

        // Tendencia de ingresos - CORREGIDO PARA MySQL
        $revenueTrend = FinancialTransaction::where('transaction_type', 'income')
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as month, SUM(amount) as revenue")
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('transaction_date', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('transaction_date', '<=', $filters['end_date']);
            })
            ->when(!empty($filters['revenue_source_id']), function ($q) use ($filters) {
                $q->whereHas('invoice', function ($q) use ($filters) {
                    $q->where('revenue_source_id', $filters['revenue_source_id']);
                });
            })
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Pagos pendientes
        $pendingPayments = Invoice::where('status', 'Pending')
            ->selectRaw('COUNT(*) as count, SUM(total_amount) as total_amount')
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('issue_date', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('issue_date', '<=', $filters['end_date']);
            })
            ->when(!empty($filters['revenue_source_id']), function ($q) use ($filters) {
                $q->where('revenue_source_id', $filters['revenue_source_id']);
            })
            ->first();

        // Métricas adicionales
        $paidInvoices = Invoice::where('status', 'Paid')
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('issue_date', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('issue_date', '<=', $filters['end_date']);
            })
            ->when(!empty($filters['revenue_source_id']), function ($q) use ($filters) {
                $q->where('revenue_source_id', $filters['revenue_source_id']);
            })
            ->count();

        $totalInvoices = Invoice::when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('issue_date', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('issue_date', '<=', $filters['end_date']);
            })
            ->when(!empty($filters['revenue_source_id']), function ($q) use ($filters) {
                $q->where('revenue_source_id', $filters['revenue_source_id']);
            })
            ->count();

        $collectionRate = $totalInvoices > 0 ? ($paidInvoices / $totalInvoices) * 100 : 0;

        return [
            'total_revenue' => (float) $totalRevenue,
            'total_expenses' => (float) $totalExpenses,
            'net_income' => (float) $netIncome,
            'by_revenue_source' => $revenueBySource,
            'revenue_trend' => $revenueTrend,
            'pending_payments' => [
                'count' => (int) ($pendingPayments->count ?? 0),
                'total_amount' => (float) ($pendingPayments->total_amount ?? 0)
            ],
            'additional_metrics' => [
                'paid_invoices' => $paidInvoices,
                'total_invoices' => $totalInvoices,
                'collection_rate' => round($collectionRate, 2),
                'average_invoice_amount' => $totalInvoices > 0 ? round($totalRevenue / $totalInvoices, 2) : 0
            ],
            'filters_applied' => [
                'start_date' => $filters['start_date'] ?? null,
                'end_date' => $filters['end_date'] ?? null,
                'revenue_source_id' => $filters['revenue_source_id'] ?? null
            ]
        ];
    }

    public function getRevenueTrend(array $filters = [])
    {
        $period = $filters['period'] ?? 'monthly';
        
        // Formato MySQL
        $format = match($period) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%x-%v',
            'monthly' => '%Y-%m',
            'quarterly' => '%Y-%q',
            'yearly' => '%Y',
            default => '%Y-%m'
        };

        $labelFormat = match($period) {
            'daily' => '%d/%m/%Y',
            'weekly' => 'Semana %v %Y',
            'monthly' => '%m/%Y',
            'quarterly' => 'Q%q %Y',
            'yearly' => '%Y',
            default => '%m/%Y'
        };

        $query = FinancialTransaction::where('transaction_type', 'income')
            ->selectRaw("DATE_FORMAT(transaction_date, '{$format}') as period, 
                        DATE_FORMAT(transaction_date, '{$labelFormat}') as period_label,
                        SUM(amount) as revenue")
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('transaction_date', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('transaction_date', '<=', $filters['end_date']);
            })
            ->when(!empty($filters['revenue_source_id']), function ($q) use ($filters) {
                $q->whereHas('invoice', function ($q) use ($filters) {
                    $q->where('revenue_source_id', $filters['revenue_source_id']);
                });
            });

        return $query->groupBy('period', 'period_label')
            ->orderBy('period')
            ->get();
    }

    public function getFinancialReport(array $filters = [])
    {
        return $this->getFinancialStatistics($filters);
    }

    /**
     * Obtener todas las fuentes de ingresos para el filtro
     */
    public function getRevenueSources()
    {
        return RevenueSource::select('id', 'name')
            ->orderBy('name')
            ->get();
    }
}