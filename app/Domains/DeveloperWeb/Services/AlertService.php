<?php

namespace App\Domains\DeveloperWeb\Services;

use App\Domains\DeveloperWeb\Models\Alert;
use App\Domains\DeveloperWeb\Repositories\AlertRepository;
use App\Domains\Administrator\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class AlertService
{
    public function __construct(
        private AlertRepository $alertRepository
    ) {}

    public function getAllAlerts(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->alertRepository->getAllPaginated($perPage, $filters);
    }

    public function getAlertById(int $id): ?Alert
    {
        return $this->alertRepository->findById($id);
    }

    public function createAlert(array $data): Alert
    {
        // Obtener el ID del usuario autenticado o usar uno por defecto
        $createdBy = $this->getCurrentUserId();

        // Si no hay usuario autenticado, usar el proporcionado o buscar uno por defecto
        if (!$createdBy && isset($data['created_by'])) {
            $createdBy = $data['created_by'];
        } elseif (!$createdBy) {
            $createdBy = $this->getDefaultUserId();
        }

        $validatedData = [
            'id_alert' => $this->alertRepository->getNextAlertId(),
            'message' => $data['message'],
            'type' => $data['type'],
            'status' => $data['status'],
            'link_url' => $data['link_url'] ?? null,
            'link_text' => $data['link_text'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'priority' => $data['priority'] ?? 1,
            'created_by' => $createdBy,
            'created_date' => now(),
        ];

        return $this->alertRepository->create($validatedData);
    }

    public function updateAlert(int $id, array $data): bool
    {
        $alert = $this->alertRepository->findById($id);

        if (!$alert) {
            return false;
        }

        return $this->alertRepository->update($alert, $data);
    }

    public function deleteAlert(int $id): bool
    {
        $alert = $this->alertRepository->findById($id);

        if (!$alert) {
            return false;
        }

        return $this->alertRepository->delete($alert);
    }

    public function getStatusCounts(): array
    {
        return $this->alertRepository->getStatusCounts();
    }

    public function getTypeCounts(): array
    {
        return $this->alertRepository->getTypeCounts();
    }

    public function getPriorityCounts(): array
    {
        return $this->alertRepository->getPriorityCounts();
    }

    public function getActiveAlerts()
    {
        return $this->alertRepository->getActiveAlerts();
    }

    public function getHighPriorityAlerts()
    {
        return $this->alertRepository->getHighPriorityAlerts();
    }

    public function getAlertsForPublic(array $filters = []): array
    {
        $filters['active_only'] = true;
        return $this->alertRepository->getAllPaginated(20, $filters)->items();
    }

    /**
     * Obtener el ID del usuario actualmente autenticado
     */
    private function getCurrentUserId(): ?int
    {
        // Temporalmente devolvemos null hasta que tengas autenticación
        return null;

        // Cuando tengas autenticación, descomenta esto:
        // return auth()->id();
    }

    /**
     * Obtener un ID de usuario por defecto
     */
    private function getDefaultUserId(): int
    {
        try {
            // Buscar cualquier usuario existente
            $user = User::first();

            if ($user) {
                return $user->id;
            }

            // Si no hay usuarios, crear uno temporal
            return $this->createTemporaryUser();
        } catch (\Exception $e) {
            Log::error('Error al obtener usuario por defecto para alertas', [
                'error' => $e->getMessage()
            ]);

            throw new \Exception('No hay usuarios disponibles en el sistema. Por favor, crea al menos un usuario primero.');
        }
    }

    /**
     * Crear un usuario temporal para desarrollo
     */
    private function createTemporaryUser(): int
    {
        try {
            $user = User::create([
                'first_name' => 'System',
                'last_name' => 'Alert',
                'full_name' => 'System Alert',
                'email' => 'alerts@incadev.com',
                'password' => bcrypt('temporary_password'),
                'role' => json_encode(['admin']),
                'status' => 'active',
            ]);

            Log::info('Usuario temporal creado para alerts', ['user_id' => $user->id]);

            return $user->id;
        } catch (\Exception $e) {
            Log::error('Error al crear usuario temporal para alerts', [
                'error' => $e->getMessage()
            ]);

            throw new \Exception('No se pudo crear un usuario temporal. Error: ' . $e->getMessage());
        }
    }

    public function getActiveCount(): int
    {
        return $this->alertRepository->getActiveCount();
    }

    /**
     * Obtener alertas para API pública con mejor formato
     */
    /* public function getAlertsForPublicApi(array $filters = []): array
    {
        $filters['active_only'] = true;
        $alerts = $this->alertRepository->getAllPaginated(20, $filters);

        return [
            'data' => $alerts->items(),
            'meta' => [
                'current_page' => $alerts->currentPage(),
                'per_page' => $alerts->perPage(),
                'total' => $alerts->total(),
                'last_page' => $alerts->lastPage(),
            ]
        ];
    }*/
}
