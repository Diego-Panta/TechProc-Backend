<?php

namespace App\Domains\Security\Services;

use App\Domains\Security\Repositories\SecurityEventRepository;

class AnomalyDetectionService
{
    public function __construct(
        private SecurityEventRepository $eventRepository,
        private SecurityEventService $eventService
    ) {}

    /**
     * Detectar fuerza bruta (múltiples intentos de login fallidos)
     */
    public function detectBruteForce(string $email, string $ip): ?array
    {
        $failedAttempts = $this->eventRepository->countRecentFailedLogins($email, $ip, 10);

        if ($failedAttempts >= 5) {
            return [
                'type' => 'brute_force',
                'severity' => 'critical',
                'email' => $email,
                'ip' => $ip,
                'failed_attempts' => $failedAttempts,
                'message' => "Se detectaron {$failedAttempts} intentos fallidos de login en los últimos 10 minutos"
            ];
        }

        return null;
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
}
