<?php

namespace App\Domains\Users\Services;

use App\Models\User;
use IncadevUns\CoreDomain\Models\SecurityEvent;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class AdminDashboardService
{
    /**
     * Obtener estadísticas generales del dashboard de administración
     */
    public function getDashboardStats(): array
    {
        return [
            'users' => $this->getUsersStats(),
            'roles' => $this->getRolesStats(),
            'permissions' => $this->getPermissionsStats(),
            'activity' => $this->getActivityStats(),
            'recent_actions' => $this->getRecentActions(),
        ];
    }

    /**
     * Estadísticas de usuarios
     */
    private function getUsersStats(): array
    {
        $totalUsers = User::count();
        $usersWithRoles = DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->distinct('model_id')
            ->count('model_id');
        $usersWithout2FA = User::where('two_factor_enabled', false)->orWhereNull('two_factor_enabled')->count();
        $usersWith2FA = User::where('two_factor_enabled', true)->count();
        $verifiedEmails = User::whereNotNull('email_verified_at')->count();

        // Usuarios creados en los últimos 30 días
        $newUsersLast30Days = User::where('created_at', '>=', now()->subDays(30))->count();

        // Usuarios por rol (top 5) usando consulta directa
        $usersByRole = DB::table('roles')
            ->leftJoin('model_has_roles', function ($join) {
                $join->on('roles.id', '=', 'model_has_roles.role_id')
                    ->where('model_has_roles.model_type', '=', User::class);
            })
            ->select('roles.name as role', DB::raw('COUNT(model_has_roles.model_id) as count'))
            ->groupBy('roles.id', 'roles.name')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        return [
            'total' => $totalUsers,
            'with_roles' => $usersWithRoles,
            'without_roles' => $totalUsers - $usersWithRoles,
            'with_2fa' => $usersWith2FA,
            'without_2fa' => $usersWithout2FA,
            'verified_emails' => $verifiedEmails,
            'unverified_emails' => $totalUsers - $verifiedEmails,
            'new_last_30_days' => $newUsersLast30Days,
            'by_role' => $usersByRole,
        ];
    }

    /**
     * Estadísticas de roles
     */
    private function getRolesStats(): array
    {
        $totalRoles = Role::count();

        // Roles con usuarios usando consulta directa
        $rolesWithUsers = DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->distinct('role_id')
            ->count('role_id');

        $rolesWithPermissions = DB::table('role_has_permissions')
            ->distinct('role_id')
            ->count('role_id');

        // Roles con más usuarios (top 10)
        $topRoles = DB::table('roles')
            ->leftJoin('model_has_roles', function ($join) {
                $join->on('roles.id', '=', 'model_has_roles.role_id')
                    ->where('model_has_roles.model_type', '=', User::class);
            })
            ->leftJoin('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
            ->select(
                'roles.id',
                'roles.name',
                DB::raw('COUNT(DISTINCT model_has_roles.model_id) as users_count'),
                DB::raw('COUNT(DISTINCT role_has_permissions.permission_id) as permissions_count')
            )
            ->groupBy('roles.id', 'roles.name')
            ->orderByDesc('users_count')
            ->take(10)
            ->get();

        return [
            'total' => $totalRoles,
            'with_users' => $rolesWithUsers,
            'without_users' => $totalRoles - $rolesWithUsers,
            'with_permissions' => $rolesWithPermissions,
            'top_roles' => $topRoles,
        ];
    }

    /**
     * Estadísticas de permisos
     */
    private function getPermissionsStats(): array
    {
        $totalPermissions = Permission::count();

        // Permisos en uso (asignados a roles)
        $permissionsInUse = DB::table('permissions')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('role_has_permissions')
                    ->whereColumn('permissions.id', 'role_has_permissions.permission_id');
            })
            ->count();

        // Permisos por categoría (basado en el prefijo antes del punto)
        $permissionsByCategory = Permission::all()
            ->groupBy(fn ($permission) => explode('.', $permission->name)[0] ?? 'other')
            ->map(fn ($permissions) => $permissions->count())
            ->sortDesc()
            ->take(10);

        return [
            'total' => $totalPermissions,
            'in_use' => $permissionsInUse,
            'unused' => $totalPermissions - $permissionsInUse,
            'by_category' => $permissionsByCategory,
        ];
    }

    /**
     * Estadísticas de actividad (tokens/sesiones activas usando Sanctum)
     */
    private function getActivityStats(): array
    {
        // Tokens activos (no expirados)
        $activeTokens = DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->count();

        // Tokens expirados
        $expiredTokens = DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->count();

        // Usuarios con tokens activos (usuarios online/con sesión)
        $usersWithActiveTokens = DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->distinct('tokenable_id')
            ->count('tokenable_id');

        // Tokens creados hoy
        $tokensCreatedToday = DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->whereDate('created_at', today())
            ->count();

        // Tokens usados en los últimos 7 días
        $tokensUsedLast7Days = DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->where('last_used_at', '>=', now()->subDays(7))
            ->count();

        // Tokens usados en las últimas 24 horas (usuarios activos recientemente)
        $tokensUsedLast24Hours = DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->where('last_used_at', '>=', now()->subHours(24))
            ->count();

        return [
            'active_tokens' => $activeTokens,
            'expired_tokens' => $expiredTokens,
            'users_with_sessions' => $usersWithActiveTokens,
            'tokens_created_today' => $tokensCreatedToday,
            'tokens_used_last_7_days' => $tokensUsedLast7Days,
            'active_last_24_hours' => $tokensUsedLast24Hours,
        ];
    }

    /**
     * Últimas acciones realizadas en el sistema
     */
    private function getRecentActions(int $limit = 15): array
    {
        $recentEvents = SecurityEvent::with('user')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get()
            ->map(fn ($event) => [
                'id' => $event->id,
                'event_type' => $event->event_type?->value ?? $event->event_type,
                'severity' => $event->severity?->value ?? $event->severity,
                'user' => $event->user ? [
                    'id' => $event->user->id,
                    'name' => $event->user->name,
                    'email' => $event->user->email,
                ] : null,
                'ip_address' => $event->ip_address,
                'metadata' => $event->metadata,
                'created_at' => $event->created_at?->toISOString(),
            ]);

        // Estadísticas de eventos de los últimos 7 días
        $eventStats = SecurityEvent::where('created_at', '>=', now()->subDays(7))
            ->select('event_type', DB::raw('count(*) as count'))
            ->groupBy('event_type')
            ->pluck('count', 'event_type');

        // Eventos críticos recientes
        $criticalEventsCount = SecurityEvent::where('created_at', '>=', now()->subDays(7))
            ->where('severity', 'critical')
            ->count();

        return [
            'recent_events' => $recentEvents,
            'event_stats_7_days' => $eventStats,
            'critical_events_count' => $criticalEventsCount,
        ];
    }
}
