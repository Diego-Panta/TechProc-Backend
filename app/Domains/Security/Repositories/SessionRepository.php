<?php

namespace App\Domains\Security\Repositories;

use App\Domains\Security\Models\UserSession;
use App\Domains\Security\Models\ActiveToken;
use Illuminate\Support\Collection;

class SessionRepository
{
    /**
     * Obtener todas las sesiones (tokens) de un usuario
     */
    public function getByUserId(int $userId): Collection
    {
        return ActiveToken::forUser($userId)
            ->orderBy('last_used_at', 'desc')
            ->get();
    }

    /**
     * Obtener sesiones activas de un usuario
     */
    public function getActiveByUserId(int $userId): Collection
    {
        return ActiveToken::forUser($userId)
            ->active()
            ->orderBy('last_used_at', 'desc')
            ->get();
    }

    /**
     * Obtener sesión (token) por ID
     */
    public function findById(int $tokenId): ?ActiveToken
    {
        return ActiveToken::find($tokenId);
    }

    /**
     * Terminar sesión (revocar token)
     */
    public function terminate(int $tokenId): bool
    {
        $token = $this->findById($tokenId);
        return $token ? $token->delete() : false;
    }

    /**
     * Terminar todas las sesiones (tokens) de un usuario excepto el actual
     */
    public function terminateAllExceptCurrent(int $userId, int $currentTokenId): int
    {
        return ActiveToken::forUser($userId)
            ->where('id', '!=', $currentTokenId)
            ->delete();
    }

    /**
     * Detectar sesiones sospechosas (múltiples IPs)
     */
    public function getSuspiciousSessions(int $userId): Collection
    {
        $sessions = $this->getActiveByUserId($userId);

        // Si hay más de una IP activa simultáneamente, es sospechoso
        $uniqueIps = $sessions->pluck('ip_address')->unique()->filter();

        if ($uniqueIps->count() > 1) {
            return $sessions;
        }

        return collect([]);
    }

    /**
     * Contar sesiones activas por usuario
     */
    public function countActiveSessions(int $userId): int
    {
        return ActiveToken::forUser($userId)->active()->count();
    }

    /**
     * Contar todas las sesiones de un usuario (activas e inactivas)
     */
    public function countAllSessions(int $userId): int
    {
        return ActiveToken::forUser($userId)->count();
    }

    /**
     * Terminar las sesiones más antiguas de un usuario
     */
    public function terminateOldestSessions(int $userId, int $count): int
    {
        $oldestTokens = ActiveToken::forUser($userId)
            ->orderBy('last_used_at', 'asc')
            ->orderBy('created_at', 'asc')
            ->limit($count)
            ->pluck('id');

        return ActiveToken::whereIn('id', $oldestTokens)->delete();
    }
}
