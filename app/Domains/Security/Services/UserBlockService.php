<?php

namespace App\Domains\Security\Services;

use App\Domains\Security\Models\UserBlock;
use App\Domains\Security\Models\SecuritySetting;
use App\Domains\Security\Repositories\UserBlockRepository;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserBlockService
{
    public function __construct(
        private UserBlockRepository $blockRepository,
        private SecurityEventService $eventService
    ) {}

    /**
     * Verificar si un usuario está bloqueado
     */
    public function isUserBlocked(int $userId): bool
    {
        return $this->blockRepository->isUserBlocked($userId);
    }

    /**
     * Verificar si un email está bloqueado (para login)
     */
    public function isEmailBlocked(string $email): ?array
    {
        $block = $this->blockRepository->isEmailBlocked($email);

        if (!$block || !$block->is_currently_blocked) {
            return null;
        }

        return [
            'blocked' => true,
            'reason' => $block->reason,
            'blocked_at' => $block->blocked_at->toIso8601String(),
            'blocked_until' => $block->blocked_until?->toIso8601String(),
            'remaining_time' => $block->remaining_time,
            'block_type' => $block->block_type,
        ];
    }

    /**
     * Obtener el bloqueo activo de un usuario
     */
    public function getActiveBlock(int $userId): ?UserBlock
    {
        return $this->blockRepository->getActiveBlock($userId);
    }

    /**
     * Bloquear un usuario automáticamente (por intentos fallidos)
     */
    public function blockUserAutomatically(int $userId, string $ip, int $failedAttempts): UserBlock
    {
        $blockDuration = SecuritySetting::get('block_duration_minutes', 30);

        $block = $this->blockRepository->create([
            'user_id' => $userId,
            'blocked_by' => null,
            'reason' => "Bloqueado automáticamente por {$failedAttempts} intentos fallidos de login",
            'block_type' => 'automatic',
            'ip_address' => $ip,
            'blocked_at' => now(),
            'blocked_until' => now()->addMinutes($blockDuration),
            'is_active' => true,
            'metadata' => [
                'failed_attempts' => $failedAttempts,
                'block_duration_minutes' => $blockDuration,
            ],
        ]);

        // Registrar evento de seguridad
        $this->eventService->logUserBlocked($userId, $ip, '', [
            'block_type' => 'automatic',
            'failed_attempts' => $failedAttempts,
            'duration_minutes' => $blockDuration,
        ]);

        return $block;
    }

    /**
     * Bloquear un usuario manualmente (por administrador)
     */
    public function blockUserManually(
        int $userId,
        int $blockedBy,
        string $reason,
        ?int $durationMinutes = null,
        ?string $ip = null
    ): UserBlock {
        // Desactivar bloqueos anteriores
        $this->blockRepository->unblock($userId, $blockedBy);

        $block = $this->blockRepository->create([
            'user_id' => $userId,
            'blocked_by' => $blockedBy,
            'reason' => $reason,
            'block_type' => 'manual',
            'ip_address' => $ip,
            'blocked_at' => now(),
            'blocked_until' => $durationMinutes ? now()->addMinutes($durationMinutes) : null,
            'is_active' => true,
            'metadata' => [
                'duration_minutes' => $durationMinutes,
                'permanent' => is_null($durationMinutes),
            ],
        ]);

        // Registrar evento de seguridad
        $this->eventService->logUserBlocked($userId, $ip ?? '', '', [
            'block_type' => 'manual',
            'blocked_by' => $blockedBy,
            'reason' => $reason,
            'duration_minutes' => $durationMinutes,
            'permanent' => is_null($durationMinutes),
        ]);

        return $block;
    }

    /**
     * Desbloquear un usuario
     */
    public function unblockUser(int $userId, int $unblockedBy, ?string $ip = null): array
    {
        $block = $this->blockRepository->getActiveBlock($userId);

        if (!$block) {
            return [
                'success' => false,
                'message' => 'El usuario no está bloqueado',
            ];
        }

        $this->blockRepository->unblock($userId, $unblockedBy);

        // Registrar evento de seguridad
        $this->eventService->logUserUnblocked($userId, $ip ?? '', '', [
            'unblocked_by' => $unblockedBy,
            'original_block_reason' => $block->reason,
        ]);

        return [
            'success' => true,
            'message' => 'Usuario desbloqueado exitosamente',
        ];
    }

    /**
     * Desbloquear por ID de bloqueo
     */
    public function unblockById(int $blockId, int $unblockedBy, ?string $ip = null): array
    {
        $block = $this->blockRepository->findById($blockId);

        if (!$block) {
            return [
                'success' => false,
                'message' => 'Bloqueo no encontrado',
            ];
        }

        if (!$block->is_active) {
            return [
                'success' => false,
                'message' => 'El bloqueo ya está inactivo',
            ];
        }

        $this->blockRepository->unblockById($blockId, $unblockedBy);

        // Registrar evento de seguridad
        $this->eventService->logUserUnblocked($block->user_id, $ip ?? '', '', [
            'unblocked_by' => $unblockedBy,
            'block_id' => $blockId,
        ]);

        return [
            'success' => true,
            'message' => 'Usuario desbloqueado exitosamente',
        ];
    }

    /**
     * Obtener historial de bloqueos de un usuario
     */
    public function getUserBlockHistory(int $userId): Collection
    {
        return $this->blockRepository->getByUserId($userId);
    }

    /**
     * Obtener todos los usuarios bloqueados actualmente
     */
    public function getAllBlockedUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->blockRepository->getAllActiveBlocks($perPage);
    }

    /**
     * Obtener historial completo de bloqueos
     */
    public function getBlockHistory(int $perPage = 15): LengthAwarePaginator
    {
        return $this->blockRepository->getAllBlocks($perPage);
    }

    /**
     * Obtener estadísticas de bloqueos
     */
    public function getStatistics(int $days = 30): array
    {
        return $this->blockRepository->getStatistics($days);
    }

    /**
     * Desactivar bloqueos expirados (para ejecutar en cron/scheduler)
     */
    public function cleanupExpiredBlocks(): int
    {
        return $this->blockRepository->deactivateExpiredBlocks();
    }

    /**
     * Obtener resumen de bloqueo para dashboard
     */
    public function getBlockingSummary(): array
    {
        $stats = $this->getStatistics(30);

        return [
            'currently_blocked_count' => $stats['currently_blocked'],
            'total_blocks_last_30_days' => $stats['total_blocks'],
            'automatic_blocks' => $stats['automatic_blocks'],
            'manual_blocks' => $stats['manual_blocks'],
        ];
    }
}
