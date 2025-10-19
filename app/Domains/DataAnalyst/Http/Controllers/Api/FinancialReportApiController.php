<?php

namespace App\Domains\DataAnalyst\Http\Controllers\Api;

use App\Domains\DataAnalyst\Services\FinancialReportService;
use App\Domains\DataAnalyst\Http\Requests\Api\FinancialReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FinancialReportApiController
{
    public function __construct(
        private FinancialReportService $financialReportService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/data-analyst/financial/statistics",
     *     summary="Obtener estadísticas financieras completas",
     *     tags={"DataAnalyst - Financial"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar estadísticas",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar estadísticas",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="revenue_source_id",
     *         in="query",
     *         description="Filtrar por fuente de ingresos específica",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas financieras obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_revenue", type="number", format="float", example=567890.50),
     *                 @OA\Property(property="total_expenses", type="number", format="float", example=234567.80),
     *                 @OA\Property(property="net_income", type="number", format="float", example=333322.70),
     *                 @OA\Property(property="by_revenue_source", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="source_id", type="integer", example=1),
     *                         @OA\Property(property="source_name", type="string", example="Matrículas"),
     *                         @OA\Property(property="amount", type="number", format="float", example=456789.50)
     *                     )
     *                 ),
     *                 @OA\Property(property="revenue_trend", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="month", type="string", example="2025-08"),
     *                         @OA\Property(property="revenue", type="number", format="float", example=45678.90)
     *                     )
     *                 ),
     *                 @OA\Property(property="pending_payments", type="object",
     *                     @OA\Property(property="count", type="integer", example=45),
     *                     @OA\Property(property="total_amount", type="number", format="float", example=23456.70)
     *                 ),
     *                 @OA\Property(property="additional_metrics", type="object",
     *                     @OA\Property(property="paid_invoices", type="integer", example=120),
     *                     @OA\Property(property="total_invoices", type="integer", example=150),
     *                     @OA\Property(property="collection_rate", type="number", format="float", example=80.0),
     *                     @OA\Property(property="average_invoice_amount", type="number", format="float", example=4732.42)
     *                 ),
     *                 @OA\Property(property="filters_applied", type="object",
     *                     @OA\Property(property="start_date", type="string", format="date", example="2025-01-01"),
     *                     @OA\Property(property="end_date", type="string", format="date", example="2025-12-31"),
     *                     @OA\Property(property="revenue_source_id", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function getStatistics(FinancialReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $statistics = $this->financialReportService->getFinancialStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting financial statistics', [
                'filters' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas financieras',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/financial/revenue-trend",
     *     summary="Obtener tendencia de ingresos por período",
     *     tags={"DataAnalyst - Financial"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="revenue_source_id",
     *         in="query",
     *         description="Filtrar por fuente de ingresos específica",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Período de agrupación",
     *         required=false,
     *         @OA\Schema(type="string", enum={"daily", "weekly", "monthly", "quarterly", "yearly"}, default="monthly")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tendencia de ingresos obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="period", type="string", example="2025-08"),
     *                     @OA\Property(property="period_label", type="string", example="08/2025"),
     *                     @OA\Property(property="revenue", type="number", format="float", example=45678.90)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getRevenueTrend(FinancialReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $trends = $this->financialReportService->getRevenueTrend($filters);

            return response()->json([
                'success' => true,
                'data' => $trends
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting revenue trend', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la tendencia de ingresos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/financial/revenue-sources",
     *     summary="Obtener listado de fuentes de ingresos",
     *     tags={"DataAnalyst - Financial"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Fuentes de ingresos obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Matrículas"),
     *                     @OA\Property(property="description", type="string", example="Ingresos por matrículas de estudiantes")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getRevenueSources(): JsonResponse
    {
        try {
            $sources = $this->financialReportService->getRevenueSources();

            return response()->json([
                'success' => true,
                'data' => $sources
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting revenue sources', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las fuentes de ingresos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/financial/pending-payments",
     *     summary="Obtener detalles de pagos pendientes",
     *     tags={"DataAnalyst - Financial"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="revenue_source_id",
     *         in="query",
     *         description="Filtrar por fuente de ingresos específica",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=20)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Página actual",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pagos pendientes obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="summary", type="object",
     *                     @OA\Property(property="count", type="integer", example=45),
     *                     @OA\Property(property="total_amount", type="number", format="float", example=23456.70)
     *                 ),
     *                 @OA\Property(property="invoices", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="invoice_number", type="string", example="FAC-2025-001"),
     *                         @OA\Property(property="issue_date", type="string", format="date", example="2025-01-15"),
     *                         @OA\Property(property="total_amount", type="number", format="float", example=1500.00),
     *                         @OA\Property(property="revenue_source", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Matrículas")
     *                         ),
     *                         @OA\Property(property="days_overdue", type="integer", example=15)
     *                     )
     *                 ),
     *                 @OA\Property(property="pagination", type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="per_page", type="integer", example=20),
     *                     @OA\Property(property="total", type="integer", example=45)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getPendingPayments(FinancialReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $pendingPayments = $this->financialReportService->getPendingPayments($filters);

            return response()->json([
                'success' => true,
                'data' => $pendingPayments
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting pending payments', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los pagos pendientes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}