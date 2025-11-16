<?php

namespace App\Domains\Security\Services;

use App\Domains\Security\Enums\SecurityEventSeverity;
use App\Domains\Security\Enums\SecurityEventType;
use App\Domains\Security\Models\SecurityEvent;
use App\Domains\Security\Repositories\SecurityEventRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SecurityEventService
{
    public function __construct(
        private SecurityEventRepository $eventRepository
    ) {}

    /**
     * Registrar evento de seguridad
     */
    public function logEvent(
        ?int $userId,
        SecurityEventType $eventType,
        SecurityEventSeverity $severity,
        ?string $ipAddress,
        ?string $userAgent,
        ?array $metadata = null
    ): SecurityEvent {
        return $this->eventRepository->create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'severity' => $severity,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Obtener eventos del usuario
     */
    public function getMyEvents(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->eventRepository->getByUserId($userId, $perPage);
    }

    /**
     * Obtener eventos recientes
     */
    public function getRecentEvents(int $userId, int $days = 7): Collection
    {
        return $this->eventRepository->getRecentByUserId($userId, $days);
    }

    /**
     * Obtener eventos críticos
     */
    public function getCriticalEvents(int $userId, int $days = 7): Collection
    {
        return $this->eventRepository->getCriticalEvents($userId, $days);
    }

    /**
     * Obtener estadísticas
     */
    public function getStatistics(int $userId, int $days = 30): array
    {
        return $this->eventRepository->getStatistics($userId, $days);
    }

    /**
     * Registrar login exitoso
     */
    public function logLoginSuccess(int $userId, string $ip, string $userAgent): SecurityEvent
    {
        return $this->logEvent(
            $userId,
            SecurityEventType::LOGIN_SUCCESS,
            SecurityEventSeverity::INFO,
            $ip,
            $userAgent,
            ['timestamp' => now()->toIso8601String()]
        );
    }

    /**
     * Registrar login fallido
     */
    public function logLoginFailed(string $email, string $ip, string $userAgent, string $reason): SecurityEvent
    {
        return $this->logEvent(
            null,
            SecurityEventType::LOGIN_FAILED,
            SecurityEventSeverity::WARNING,
            $ip,
            $userAgent,
            [
                'email' => $email,
                'reason' => $reason,
                'timestamp' => now()->toIso8601String()
            ]
        );
    }

    /**
     * Registrar logout
     */
    public function logLogout(int $userId, string $ip, string $userAgent): SecurityEvent
    {
        return $this->logEvent(
            $userId,
            SecurityEventType::LOGOUT,
            SecurityEventSeverity::INFO,
            $ip,
            $userAgent
        );
    }

    /**
     * Registrar creación de token
     */
    public function logTokenCreated(int $userId, string $tokenName, string $ip, string $userAgent): SecurityEvent
    {
        return $this->logEvent(
            $userId,
            SecurityEventType::TOKEN_CREATED,
            SecurityEventSeverity::INFO,
            $ip,
            $userAgent,
            ['token_name' => $tokenName]
        );
    }

    /**
     * Registrar revocación de token
     */
    public function logTokenRevoked(int $userId, string $tokenName, string $ip, string $userAgent): SecurityEvent
    {
        return $this->logEvent(
            $userId,
            SecurityEventType::TOKEN_REVOKED,
            SecurityEventSeverity::INFO,
            $ip,
            $userAgent,
            ['token_name' => $tokenName]
        );
    }

    /**
     * Registrar sesión terminada
     */
    public function logSessionTerminated(int $userId, string $sessionIp, string $ip, string $userAgent): SecurityEvent
    {
        return $this->logEvent(
            $userId,
            SecurityEventType::SESSION_TERMINATED,
            SecurityEventSeverity::INFO,
            $ip,
            $userAgent,
            ['terminated_session_ip' => $sessionIp]
        );
    }

    /**
     * Registrar anomalía detectada
     */
    public function logAnomalyDetected(int $userId, string $anomalyType, array $details, string $ip, string $userAgent): SecurityEvent
    {
        return $this->logEvent(
            $userId,
            SecurityEventType::ANOMALY_DETECTED,
            SecurityEventSeverity::CRITICAL,
            $ip,
            $userAgent,
            [
                'anomaly_type' => $anomalyType,
                'details' => $details
            ]
        );
    }
}
