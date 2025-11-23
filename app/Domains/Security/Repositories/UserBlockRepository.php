<?php

namespace App\Domains\Security\Repositories;

use App\Models\User;
use IncadevUns\CoreDomain\Models\UserBlock;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class UserBlockRepository
{
    /**
     * Crear un nuevo bloqueo
     */
    public function create(array $data): UserBlock
    {
        return UserBlock::create($data);
    }

    /**
     * Verificar si un usuario está actualmente bloqueado
     */
    public function isUserBlocked(int $userId): bool
    {
        return UserBlock::forUser($userId)->currentlyBlocked()->exists();
    }

    /**
     * Obtener el bloqueo activo de un usuario
     */
    public function getActiveBlock(int $userId): ?UserBlock
    {
        return UserBlock::forUser($userId)
            ->currentlyBlocked()
            ->orderBy('blocked_at', 'desc')
            ->first();
    }

    /**
     * Obtener todos los bloqueos de un usuario
     */
    public function getByUserId(int $userId): Collection
    {
        return UserBlock::forUser($userId)
            ->with(['blockedByUser', 'unblockedByUser'])
            ->orderBy('blocked_at', 'desc')
            ->get();
    }

    /**
     * Obtener todos los bloqueos activos paginados
     */
    public function getAllActiveBlocks(int $perPage = 15): LengthAwarePaginator
    {
        return UserBlock::currentlyBlocked()
            ->with(['user', 'blockedByUser'])
            ->orderBy('blocked_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtener todos los bloqueos (historial) paginados
     */
    public function getAllBlocks(int $perPage = 15): LengthAwarePaginator
    {
        return UserBlock::with(['user', 'blockedByUser', 'unblockedByUser'])
            ->orderBy('blocked_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Desbloquear un usuario
     */
    public function unblock(int $userId, ?int $unblockedBy = null): bool
    {
        return UserBlock::forUser($userId)
            ->currentlyBlocked()
            ->update([
                'is_active' => false,
                'unblocked_at' => now(),
                'unblocked_by' => $unblockedBy,
            ]) > 0;
    }

    /**
     * Desbloquear por ID de bloqueo
     */
    public function unblockById(int $blockId, ?int $unblockedBy = null): bool
    {
        $block = UserBlock::find($blockId);

        if (!$block || !$block->is_active) {
            return false;
        }

        return $block->update([
            'is_active' => false,
            'unblocked_at' => now(),
            'unblocked_by' => $unblockedBy,
        ]);
    }

    /**
     * Desactivar bloqueos expirados
     */
    public function deactivateExpiredBlocks(): int
    {
        return UserBlock::expired()
            ->update([
                'is_active' => false,
                'unblocked_at' => now(),
            ]);
    }

    /**
     * Contar usuarios bloqueados actualmente
     */
    public function countBlockedUsers(): int
    {
        return UserBlock::currentlyBlocked()
            ->distinct('user_id')
            ->count('user_id');
    }

    /**
     * Obtener estadísticas de bloqueos
     */
    public function getStatistics(int $days = 30): array
    {
        $since = Carbon::now()->subDays($days);

        return [
            'total_blocks' => UserBlock::where('blocked_at', '>=', $since)->count(),
            'automatic_blocks' => UserBlock::automatic()->where('blocked_at', '>=', $since)->count(),
            'manual_blocks' => UserBlock::manual()->where('blocked_at', '>=', $since)->count(),
            'currently_blocked' => $this->countBlockedUsers(),
            'unblocked' => UserBlock::where('blocked_at', '>=', $since)
                ->whereNotNull('unblocked_at')
                ->count(),
        ];
    }

    /**
     * Buscar usuario por email y verificar bloqueo
     */
    public function isEmailBlocked(string $email): ?UserBlock
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return null;
        }

        return $this->getActiveBlock($user->id);
    }

    /**
     * Obtener bloqueo por ID
     */
    public function findById(int $blockId): ?UserBlock
    {
        return UserBlock::with(['user', 'blockedByUser', 'unblockedByUser'])
            ->find($blockId);
    }
}
