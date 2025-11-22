<?php

namespace App\Domains\Security\Services;

use App\Domains\Security\Repositories\SessionRepository;
use App\Domains\Security\Models\SecuritySetting;
use Illuminate\Support\Collection;

class SessionService
{
    public function __construct(
        private SessionRepository $sessionRepository
    ) {}

    /**
     * Verificar si el usuario ha alcanzado el máximo de sesiones concurrentes
     */
    public function hasReachedMaxSessions(int $userId): bool
    {
        $maxSessions = SecuritySetting::get('max_concurrent_sessions', 5);
        $currentSessions = $this->sessionRepository->countActiveSessions($userId);

        return $currentSessions >= $maxSessions;
    }

    /**
     * Obtener información de sesiones concurrentes
     */
    public function getConcurrentSessionsInfo(int $userId): array
    {
        $maxSessions = SecuritySetting::get('max_concurrent_sessions', 5);
        $currentSessions = $this->sessionRepository->countActiveSessions($userId);

        return [
            'current_sessions' => $currentSessions,
            'max_sessions' => $maxSessions,
            'has_reached_limit' => $currentSessions >= $maxSessions,
        ];
    }

    /**
     * Terminar las sesiones más antiguas si se excede el límite
     * Retorna el número de sesiones terminadas
     */
    public function terminateOldestIfExceedsLimit(int $userId): int
    {
        $maxSessions = SecuritySetting::get('max_concurrent_sessions', 5);
        $currentSessions = $this->sessionRepository->countActiveSessions($userId);

        if ($currentSessions < $maxSessions) {
            return 0;
        }

        // Calcular cuántas sesiones hay que eliminar
        $sessionsToTerminate = ($currentSessions - $maxSessions) + 1;

        return $this->sessionRepository->terminateOldestSessions($userId, $sessionsToTerminate);
    }

    /**
     * Obtener sesiones del usuario autenticado
     */
    public function getMySessions(int $userId): Collection
    {
        return $this->sessionRepository->getByUserId($userId);
    }

    /**
     * Obtener sesiones activas del usuario
     */
    public function getActiveSessions(int $userId): Collection
    {
        return $this->sessionRepository->getActiveByUserId($userId);
    }

    /**
     * Terminar una sesión específica (revocar token)
     */
    public function terminateSession(int $tokenId, int $userId): array
    {
        $token = $this->sessionRepository->findById($tokenId);

        if (!$token) {
            return [
                'success' => false,
                'message' => 'Sesión no encontrada',
            ];
        }

        // Verificar que el token pertenece al usuario
        if ($token->tokenable_id !== $userId) {
            return [
                'success' => false,
                'message' => 'No tienes permiso para terminar esta sesión',
            ];
        }

        $terminated = $this->sessionRepository->terminate($tokenId);

        return [
            'success' => $terminated,
            'message' => $terminated ? 'Sesión terminada exitosamente' : 'Error al terminar la sesión',
        ];
    }

    /**
     * Terminar todas las sesiones (tokens) excepto el actual
     */
    public function terminateAllExceptCurrent(int $userId, int $currentTokenId): array
    {
        $count = $this->sessionRepository->terminateAllExceptCurrent($userId, $currentTokenId);

        return [
            'success' => true,
            'message' => "Se terminaron $count sesiones",
            'count' => $count,
        ];
    }

    /**
     * Detectar sesiones sospechosas
     */
    public function getSuspiciousSessions(int $userId): Collection
    {
        return $this->sessionRepository->getSuspiciousSessions($userId);
    }

    /**
     * Obtener resumen de sesiones
     */
    public function getSessionsSummary(int $userId): array
    {
        $activeSessions = $this->getActiveSessions($userId);
        $suspiciousSessions = $this->getSuspiciousSessions($userId);

        return [
            'total_active' => $activeSessions->count(),
            'unique_ips' => $activeSessions->pluck('ip_address')->unique()->count(),
            'has_suspicious' => $suspiciousSessions->isNotEmpty(),
            'suspicious_count' => $suspiciousSessions->count(),
        ];
    }
}
