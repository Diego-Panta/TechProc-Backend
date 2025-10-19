<?php

namespace App\Domains\DataAnalyst\Http\Controllers\Api;

use App\Domains\DataAnalyst\Services\SecurityReportService;
use App\Domains\DataAnalyst\Http\Requests\Api\SecurityReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecurityReportApiController
{
    public function __construct(
        private SecurityReportService $securityReportService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/data-analyst/security/analysis",
     *     summary="Obtener análisis completo de seguridad",
     *     tags={"DataAnalyst - Security"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar análisis",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar análisis",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="event_type",
     *         in="query",
     *         description="Filtrar por tipo de evento específico",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="severity",
     *         in="query",
     *         description="Filtrar por severidad de alerta",
     *         required=false,
     *         @OA\Schema(type="string", enum={"low", "medium", "high", "critical"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Análisis de seguridad obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_security_events", type="integer", example=892),
     *                 @OA\Property(property="by_event_type", type="object",
     *                     @OA\Property(property="login_success", type="integer", example=567),
     *                     @OA\Property(property="login_failure", type="integer", example=89),
     *                     @OA\Property(property="password_change", type="integer", example=45),
     *                     @OA\Property(property="suspicious_activity", type="integer", example=12),
     *                     @OA\Property(property="unauthorized_access_attempt", type="integer", example=8)
     *                 ),
     *                 @OA\Property(property="blocked_ips", type="object",
     *                     @OA\Property(property="total", type="integer", example=12),
     *                     @OA\Property(property="active", type="integer", example=8),
     *                     @OA\Property(property="this_period", type="integer", example=3)
     *                 ),
     *                 @OA\Property(property="security_alerts", type="object",
     *                     @OA\Property(property="total", type="integer", example=23),
     *                     @OA\Property(property="by_severity", type="object",
     *                         @OA\Property(property="low", type="integer", example=8),
     *                         @OA\Property(property="medium", type="integer", example=10),
     *                         @OA\Property(property="high", type="integer", example=5)
     *                     )
     *                 ),
     *                 @OA\Property(property="incidents", type="object",
     *                     @OA\Property(property="total", type="integer", example=8),
     *                     @OA\Property(property="resolved", type="integer", example=5),
     *                     @OA\Property(property="in_progress", type="integer", example=3)
     *                 ),
     *                 @OA\Property(property="failed_login_rate", type="number", format="float", example=13.5),
     *                 @OA\Property(property="top_threat_ips", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="ip_address", type="string", example="203.45.67.89"),
     *                         @OA\Property(property="attempt_count", type="integer", example=45),
     *                         @OA\Property(property="blocked", type="boolean", example=true)
     *                     )
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
    public function getAnalysis(SecurityReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $analysis = $this->securityReportService->getSecurityAnalysis($filters);

            return response()->json([
                'success' => true,
                'data' => $analysis
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting security analysis', [
                'filters' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el análisis de seguridad',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/security/events",
     *     summary="Obtener listado de eventos de seguridad",
     *     tags={"DataAnalyst - Security"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar eventos",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar eventos",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="event_type",
     *         in="query",
     *         description="Filtrar por tipo de evento específico",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="ip_address",
     *         in="query",
     *         description="Filtrar por dirección IP específica",
     *         required=false,
     *         @OA\Schema(type="string", format="ipv4")
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
     *         description="Listado de eventos de seguridad obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="id_security_log", type="integer", example=1001),
     *                         @OA\Property(property="event_type", type="string", example="login_failure"),
     *                         @OA\Property(property="description", type="string", example="Intento de login fallido"),
     *                         @OA\Property(property="source_ip", type="string", example="192.168.1.100"),
     *                         @OA\Property(property="event_date", type="string", format="date-time"),
     *                         @OA\Property(property="user", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Juan Pérez"),
     *                             @OA\Property(property="email", type="string", example="juan@example.com")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="links", type="object"),
     *                 @OA\Property(property="meta", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function getEvents(SecurityReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $events = $this->securityReportService->getSecurityEvents($filters);

            return response()->json([
                'success' => true,
                'data' => $events
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting security events', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los eventos de seguridad',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/security/alerts",
     *     summary="Obtener listado de alertas de seguridad",
     *     tags={"DataAnalyst - Security"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar alertas",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar alertas",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="severity",
     *         in="query",
     *         description="Filtrar por severidad",
     *         required=false,
     *         @OA\Schema(type="string", enum={"low", "medium", "high", "critical"})
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por estado de alerta",
     *         required=false,
     *         @OA\Schema(type="string", enum={"new", "investigating", "resolved"})
     *     ),
     *     @OA\Parameter(
     *         name="threat_type",
     *         in="query",
     *         description="Filtrar por tipo de amenaza",
     *         required=false,
     *         @OA\Schema(type="string")
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
     *         description="Listado de alertas de seguridad obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="id_security_alert", type="integer", example=2001),
     *                         @OA\Property(property="threat_type", type="string", example="multiple_failed_logins"),
     *                         @OA\Property(property="severity", type="string", example="high"),
     *                         @OA\Property(property="status", type="string", example="investigating"),
     *                         @OA\Property(property="detection_date", type="string", format="date-time"),
     *                         @OA\Property(property="blocked_ip", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="ip_address", type="string", example="192.168.1.100"),
     *                             @OA\Property(property="reason", type="string", example="Múltiples intentos de login fallidos"),
     *                             @OA\Property(property="active", type="boolean", example=true)
     *                         ),
     *                         @OA\Property(property="incidents_count", type="integer", example=1)
     *                     )
     *                 ),
     *                 @OA\Property(property="links", type="object"),
     *                 @OA\Property(property="meta", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function getAlerts(SecurityReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $alerts = $this->securityReportService->getSecurityAlerts($filters);

            return response()->json([
                'success' => true,
                'data' => $alerts
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting security alerts', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las alertas de seguridad',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/security/dashboard",
     *     summary="Obtener datos consolidados para dashboard de seguridad",
     *     tags={"DataAnalyst - Security"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar datos",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar datos",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos del dashboard obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="summary", type="object",
     *                     @OA\Property(property="total_events", type="integer", example=892),
     *                     @OA\Property(property="total_alerts", type="integer", example=23),
     *                     @OA\Property(property="total_incidents", type="integer", example=8),
     *                     @OA\Property(property="active_blocked_ips", type="integer", example=8)
     *                 ),
     *                 @OA\Property(property="recent_events", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="event_type", type="string", example="login_failure"),
     *                         @OA\Property(property="source_ip", type="string", example="192.168.1.100"),
     *                         @OA\Property(property="event_date", type="string", format="date-time")
     *                     )
     *                 ),
     *                 @OA\Property(property="alerts_by_severity", type="object",
     *                     @OA\Property(property="critical", type="integer", example=2),
     *                     @OA\Property(property="high", type="integer", example=5),
     *                     @OA\Property(property="medium", type="integer", example=10),
     *                     @OA\Property(property="low", type="integer", example=6)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getDashboardData(SecurityReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            
            // Obtener datos consolidados
            $analysis = $this->securityReportService->getSecurityAnalysis($filters);
            $recentEvents = $this->securityReportService->getSecurityEvents(array_merge($filters, ['per_page' => 5]));
            $alerts = $this->securityReportService->getSecurityAlerts(array_merge($filters, ['per_page' => 10]));

            $dashboardData = [
                'summary' => [
                    'total_events' => $analysis['total_security_events'],
                    'total_alerts' => $analysis['security_alerts']['total'],
                    'total_incidents' => $analysis['incidents']['total'],
                    'active_blocked_ips' => $analysis['blocked_ips']['active'],
                    'failed_login_rate' => $analysis['failed_login_rate']
                ],
                'recent_events' => $recentEvents->items(),
                'alerts_by_severity' => $analysis['security_alerts']['by_severity'],
                'events_by_type' => $analysis['by_event_type']
            ];

            return response()->json([
                'success' => true,
                'data' => $dashboardData
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting security dashboard data', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos del dashboard de seguridad',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}