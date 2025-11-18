<?php

namespace App\Domains\Security\Repositories;

use IncadevUns\CoreDomain\Enums\SecurityEventSeverity;
use IncadevUns\CoreDomain\Enums\SecurityEventType;
use IncadevUns\CoreDomain\Models\SecurityEvent;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SecurityEventRepository
{
    /**
     * Crear un evento de seguridad
     */
    public function create(array $data): SecurityEvent
    {
        return SecurityEvent::create($data);
    }

    /**
     * Obtener eventos de un usuario
     */
    public function getByUserId(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return SecurityEvent::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtener eventos recientes de un usuario
     */
    public function getRecentByUserId(int $userId, int $days = 7): Collection
    {
        return SecurityEvent::forUser($userId)
            ->recent($days)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtener eventos críticos
     */
    public function getCriticalEvents(int $userId, int $days = 7): Collection
    {
        return SecurityEvent::forUser($userId)
            ->critical()
            ->recent($days)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtener eventos por tipo
     */
    public function getByType(int $userId, SecurityEventType $type, int $days = 30): Collection
    {
        return SecurityEvent::forUser($userId)
            ->ofType($type)
            ->recent($days)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtener eventos por IP
     */
    public function getByIp(string $ip, int $days = 7): Collection
    {
        return SecurityEvent::fromIp($ip)
            ->recent($days)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Contar eventos de login fallidos recientes
     */
    public function countRecentFailedLogins(string $email, string $ip, int $minutes = 10): int
    {
        return SecurityEvent::where('event_type', SecurityEventType::LOGIN_FAILED->value)
            ->where('ip_address', $ip)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->whereJsonContains('metadata->email', $email)
            ->count();
    }

    /**
     * Detectar anomalías: Múltiples IPs en poco tiempo
     */
    public function detectMultipleIpLogins(int $userId, int $minutes = 30): ?array
    {
        $events = SecurityEvent::forUser($userId)
            ->ofType(SecurityEventType::LOGIN_SUCCESS)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->get();

        $uniqueIps = $events->pluck('ip_address')->unique();

        if ($uniqueIps->count() > 1) {
            return [
                'detected' => true,
                'ips' => $uniqueIps->toArray(),
                'count' => $uniqueIps->count(),
                'timeframe' => $minutes,
            ];
        }

        return null;
    }

    /**
     * Estadísticas de eventos
     */
    public function getStatistics(int $userId, int $days = 30): array
    {
        $events = SecurityEvent::forUser($userId)->recent($days)->get();

        return [
            'total' => $events->count(),
            'by_type' => $events->groupBy('event_type')->map->count(),
            'by_severity' => $events->groupBy('severity')->map->count(),
            'critical_count' => $events->where('severity', SecurityEventSeverity::CRITICAL->value)->count(),
            'warning_count' => $events->where('severity', SecurityEventSeverity::WARNING->value)->count(),
            'info_count' => $events->where('severity', SecurityEventSeverity::INFO->value)->count(),
        ];
    }
}
