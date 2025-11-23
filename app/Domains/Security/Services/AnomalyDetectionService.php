<?php

namespace App\Domains\Security\Services;

use App\Domains\Security\Models\SecuritySetting;
use App\Domains\Security\Repositories\SecurityEventRepository;
use App\Domains\Security\Repositories\UserBlockRepository;
use App\Models\User;

class AnomalyDetectionService
{
    public function __construct(
        private SecurityEventRepository $eventRepository,
        private SecurityEventService $eventService,
        private UserBlockRepository $blockRepository
    ) {}

    /**
     * Detectar fuerza bruta (múltiples intentos de login fallidos)
     * Usa configuración dinámica para determinar umbrales
     */
    public function detectBruteForce(string $email, string $ip): ?array
    {
        // Obtener configuración dinámica
        $maxAttempts = SecuritySetting::get('max_failed_login_attempts', 5);
        $windowMinutes = SecuritySetting::get('failed_login_window_minutes', 10);

        $failedAttempts = $this->eventRepository->countRecentFailedLogins($email, $ip, $windowMinutes);

        if ($failedAttempts >= $maxAttempts) {
            return [
                'type' => 'brute_force',
                'severity' => 'critical',
                'email' => $email,
                'ip' => $ip,
                'failed_attempts' => $failedAttempts,
                'max_attempts' => $maxAttempts,
                'window_minutes' => $windowMinutes,
                'message' => "Se detectaron {$failedAttempts} intentos fallidos de login en los últimos {$windowMinutes} minutos"
            ];
        }

        return null;
    }

    /**
     * Detectar fuerza bruta y bloquear usuario si corresponde
     * Retorna información del bloqueo si se creó uno
     */
    public function detectAndBlockIfNeeded(string $email, string $ip): ?array
    {
        $bruteForce = $this->detectBruteForce($email, $ip);

        if (!$bruteForce) {
            return null;
        }

        // Buscar el usuario por email
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Si no existe el usuario, solo reportar la anomalía
            return $bruteForce;
        }

        // Verificar si ya está bloqueado
        if ($this->blockRepository->isUserBlocked($user->id)) {
            $activeBlock = $this->blockRepository->getActiveBlock($user->id);
            return [
                'type' => 'already_blocked',
                'user_id' => $user->id,
                'email' => $email,
                'remaining_time' => $activeBlock?->remaining_time,
                'blocked_until' => $activeBlock?->blocked_until?->toIso8601String(),
                'message' => 'El usuario ya está bloqueado'
            ];
        }

        // Bloquear al usuario automáticamente
        $blockDuration = SecuritySetting::get('block_duration_minutes', 30);

        $block = $this->blockRepository->create([
            'user_id' => $user->id,
            'blocked_by' => null,
            'reason' => "Bloqueado automáticamente por {$bruteForce['failed_attempts']} intentos fallidos de login en {$bruteForce['window_minutes']} minutos",
            'block_type' => 'automatic',
            'ip_address' => $ip,
            'blocked_at' => now(),
            'blocked_until' => now()->addMinutes($blockDuration),
            'is_active' => true,
            'metadata' => [
                'failed_attempts' => $bruteForce['failed_attempts'],
                'max_attempts' => $bruteForce['max_attempts'],
                'window_minutes' => $bruteForce['window_minutes'],
                'block_duration_minutes' => $blockDuration,
            ],
        ]);

        // Registrar evento de seguridad
        $this->eventService->logUserBlocked($user->id, $ip, '', [
            'block_type' => 'automatic',
            'failed_attempts' => $bruteForce['failed_attempts'],
            'duration_minutes' => $blockDuration,
        ]);

        // Registrar anomalía
        $this->eventService->logAnomalyDetected(
            $user->id,
            'brute_force_blocked',
            $bruteForce,
            $ip,
            ''
        );

        return [
            'type' => 'user_blocked',
            'user_id' => $user->id,
            'email' => $email,
            'failed_attempts' => $bruteForce['failed_attempts'],
            'block_duration_minutes' => $blockDuration,
            'blocked_until' => $block->blocked_until->toIso8601String(),
            'remaining_time' => $block->remaining_time,
            'message' => "Usuario bloqueado por {$blockDuration} minutos debido a múltiples intentos fallidos"
        ];
    }

    /**
     * Verificar si un email está bloqueado
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
     * Detectar múltiples IPs de login en poco tiempo
     */
    public function detectMultipleIpLogins(int $userId): ?array
    {
        return $this->eventRepository->detectMultipleIpLogins($userId, 30);
    }

    /**
     * Ejecutar todas las detecciones
     */
    public function runAllDetections(int $userId, string $ip, string $userAgent): array
    {
        $anomalies = [];

        // Detectar múltiples IPs
        $multipleIps = $this->detectMultipleIpLogins($userId);
        if ($multipleIps) {
            $anomalies[] = $multipleIps;

            // Registrar evento
            $this->eventService->logAnomalyDetected(
                $userId,
                'multiple_ip_logins',
                $multipleIps,
                $ip,
                $userAgent
            );
        }

        return $anomalies;
    }

    /**
     * Obtener configuración actual de detección
     */
    public function getDetectionConfig(): array
    {
        return [
            'max_failed_login_attempts' => SecuritySetting::get('max_failed_login_attempts', 5),
            'failed_login_window_minutes' => SecuritySetting::get('failed_login_window_minutes', 10),
            'block_duration_minutes' => SecuritySetting::get('block_duration_minutes', 30),
        ];
    }
}
