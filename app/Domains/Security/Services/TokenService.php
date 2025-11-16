<?php

namespace App\Domains\Security\Services;

use App\Domains\Security\Repositories\TokenRepository;
use Illuminate\Support\Collection;

class TokenService
{
    public function __construct(
        private TokenRepository $tokenRepository
    ) {}

    /**
     * Obtener tokens del usuario
     */
    public function getMyTokens(int $userId): Collection
    {
        return $this->tokenRepository->getActiveByUserId($userId);
    }

    /**
     * Revocar un token
     */
    public function revokeToken(int $tokenId, int $userId): array
    {
        $token = $this->tokenRepository->findById($tokenId);

        if (!$token) {
            return [
                'success' => false,
                'message' => 'Token no encontrado',
            ];
        }

        // Verificar que el token pertenece al usuario
        if ($token->tokenable_id !== $userId) {
            return [
                'success' => false,
                'message' => 'No tienes permiso para revocar este token',
            ];
        }

        $revoked = $this->tokenRepository->revoke($tokenId);

        return [
            'success' => $revoked,
            'message' => $revoked ? 'Token revocado exitosamente' : 'Error al revocar el token',
        ];
    }

    /**
     * Revocar todos los tokens
     */
    public function revokeAllTokens(int $userId): array
    {
        $count = $this->tokenRepository->revokeAllForUser($userId);

        return [
            'success' => true,
            'message' => "Se revocaron $count tokens",
            'count' => $count,
        ];
    }

    /**
     * Obtener tokens inactivos
     */
    public function getInactiveTokens(int $userId, int $days = 30): Collection
    {
        return $this->tokenRepository->getInactiveTokens($userId, $days);
    }

    /**
     * Obtener resumen de tokens
     */
    public function getTokensSummary(int $userId): array
    {
        $activeTokens = $this->getMyTokens($userId);
        $inactiveTokens = $this->getInactiveTokens($userId);
        $expiringTokens = $this->tokenRepository->getExpiringTokens($userId);

        return [
            'total_active' => $activeTokens->count(),
            'total_inactive' => $inactiveTokens->count(),
            'total_expiring_soon' => $expiringTokens->count(),
            'last_used' => $activeTokens->max('last_used_at'),
        ];
    }
}
