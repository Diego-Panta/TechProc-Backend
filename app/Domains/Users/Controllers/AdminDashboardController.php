<?php

namespace App\Domains\Users\Controllers;

use App\Domains\Users\Services\AdminDashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __construct(
        private AdminDashboardService $dashboardService
    ) {}

    /**
     * Obtener estadísticas del dashboard de administración
     *
     * @OA\Get(
     *     path="/admin/dashboard",
     *     summary="Dashboard de administración",
     *     description="Obtiene estadísticas generales para el panel de administración: usuarios, roles, permisos, sesiones activas y últimas acciones del sistema.",
     *     operationId="getAdminDashboard",
     *     tags={"Admin"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Dashboard de administración"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="users",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=150),
     *                     @OA\Property(property="with_roles", type="integer", example=145),
     *                     @OA\Property(property="without_roles", type="integer", example=5),
     *                     @OA\Property(property="with_2fa", type="integer", example=50),
     *                     @OA\Property(property="without_2fa", type="integer", example=100),
     *                     @OA\Property(property="verified_emails", type="integer", example=140),
     *                     @OA\Property(property="unverified_emails", type="integer", example=10),
     *                     @OA\Property(property="new_last_30_days", type="integer", example=25),
     *                     @OA\Property(
     *                         property="by_role",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="role", type="string", example="student"),
     *                             @OA\Property(property="count", type="integer", example=80)
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="roles",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=18),
     *                     @OA\Property(property="with_users", type="integer", example=15),
     *                     @OA\Property(property="without_users", type="integer", example=3),
     *                     @OA\Property(property="with_permissions", type="integer", example=18),
     *                     @OA\Property(
     *                         property="top_roles",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="admin"),
     *                             @OA\Property(property="users_count", type="integer", example=5),
     *                             @OA\Property(property="permissions_count", type="integer", example=50)
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=200),
     *                     @OA\Property(property="in_use", type="integer", example=180),
     *                     @OA\Property(property="unused", type="integer", example=20),
     *                     @OA\Property(property="by_category", type="object", example={"users": 10, "roles": 5, "tickets": 8})
     *                 ),
     *                 @OA\Property(
     *                     property="activity",
     *                     type="object",
     *                     @OA\Property(property="active_sessions", type="integer", example=45),
     *                     @OA\Property(property="blocked_sessions", type="integer", example=2),
     *                     @OA\Property(property="users_online", type="integer", example=30),
     *                     @OA\Property(property="sessions_today", type="integer", example=120),
     *                     @OA\Property(property="sessions_last_7_days", type="integer", example=500),
     *                     @OA\Property(property="device_distribution", type="object", example={"desktop": 30, "mobile": 12, "tablet": 3})
     *                 ),
     *                 @OA\Property(
     *                     property="recent_actions",
     *                     type="object",
     *                     @OA\Property(
     *                         property="recent_events",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="event_type", type="string", example="login_success"),
     *                             @OA\Property(property="severity", type="string", example="info"),
     *                             @OA\Property(
     *                                 property="user",
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="Admin User"),
     *                                 @OA\Property(property="email", type="string", example="admin@example.com")
     *                             ),
     *                             @OA\Property(property="ip_address", type="string", example="192.168.1.1"),
     *                             @OA\Property(property="created_at", type="string", example="2025-11-23T10:30:00.000000Z")
     *                         )
     *                     ),
     *                     @OA\Property(property="event_stats_7_days", type="object", example={"login_success": 200, "login_failed": 15}),
     *                     @OA\Property(property="critical_events_count", type="integer", example=3)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado - Permisos insuficientes"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\User::class);

        try {
            $stats = $this->dashboardService->getDashboardStats();

            return response()->json([
                'success' => true,
                'message' => 'Dashboard de administración',
                'data' => $stats,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas del dashboard',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
