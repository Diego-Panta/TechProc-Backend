<?php

namespace App\Domains\Security\Repositories;

use App\Models\User;
use Illuminate\Support\Collection;
use Laravel\Sanctum\PersonalAccessToken;

class TokenRepository
{
    /**
     * Obtener todos los tokens de un usuario
     */
    public function getByUserId(int $userId): Collection
    {
        return PersonalAccessToken::where('tokenable_type', User::class)
            ->where('tokenable_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtener tokens activos de un usuario
     */
    public function getActiveByUserId(int $userId): Collection
    {
        return PersonalAccessToken::where('tokenable_type', User::class)
            ->where('tokenable_id', $userId)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('last_used_at', 'desc')
            ->get();
    }

    /**
     * Obtener token por ID
     */
    public function findById(int $tokenId): ?PersonalAccessToken
    {
        return PersonalAccessToken::find($tokenId);
    }

    /**
     * Revocar token
     */
    public function revoke(int $tokenId): bool
    {
        $token = $this->findById($tokenId);
        return $token ? $token->delete() : false;
    }

    /**
     * Revocar todos los tokens de un usuario
     */
    public function revokeAllForUser(int $userId): int
    {
        return PersonalAccessToken::where('tokenable_type', User::class)
            ->where('tokenable_id', $userId)
            ->delete();
    }

    /**
     * Obtener tokens inactivos (sin uso en X días)
     */
    public function getInactiveTokens(int $userId, int $days = 30): Collection
    {
        return PersonalAccessToken::where('tokenable_type', User::class)
            ->where('tokenable_id', $userId)
            ->where(function ($query) use ($days) {
                $query->where('last_used_at', '<', now()->subDays($days))
                    ->orWhereNull('last_used_at');
            })
            ->get();
    }

    /**
     * Obtener tokens por expirar (próximos X días)
     */
    public function getExpiringTokens(int $userId, int $days = 7): Collection
    {
        return PersonalAccessToken::where('tokenable_type', User::class)
            ->where('tokenable_id', $userId)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($days)])
            ->get();
    }

    /**
     * Contar tokens activos
     */
    public function countActiveTokens(int $userId): int
    {
        return $this->getActiveByUserId($userId)->count();
    }
}
