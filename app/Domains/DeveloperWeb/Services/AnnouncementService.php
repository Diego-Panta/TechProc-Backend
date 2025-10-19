<?php

namespace App\Domains\DeveloperWeb\Services;

use App\Domains\DeveloperWeb\Models\Announcement;
use App\Domains\DeveloperWeb\Repositories\AnnouncementRepository;
use App\Domains\Administrator\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class AnnouncementService
{
    public function __construct(
        private AnnouncementRepository $announcementRepository
    ) {}

    public function getAllAnnouncements(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->announcementRepository->getAllPaginated($perPage, $filters);
    }

    /*public function getAllAnnouncementsWithFilters(array $filters = [], int $perPage = 15)
    {
        return $this->announcementRepository->getAllPaginated($perPage, $filters);
    }*/

    public function getAnnouncementById(int $id): ?Announcement
    {
        return $this->announcementRepository->findById($id);
    }

    public function createAnnouncement(array $data): Announcement
    {
        // Obtener el ID del usuario autenticado o usar uno por defecto
        $createdBy = $this->getCurrentUserId();

        // Si no hay usuario autenticado, buscar un usuario admin o crear uno temporal
        if (!$createdBy) {
            $createdBy = $this->getDefaultUserId();
        }

        $validatedData = [
            'id_announcement' => $this->announcementRepository->getNextAnnouncementId(),
            'title' => $data['title'],
            'content' => $data['content'],
            'image_url' => $data['image_url'] ?? null,
            'display_type' => $data['display_type'],
            'target_page' => $data['target_page'],
            'link_url' => $data['link_url'] ?? null,
            'button_text' => $data['button_text'] ?? null,
            'status' => $data['status'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'views' => 0,
            'created_by' => $createdBy,
            'created_date' => now(),
        ];

        return $this->announcementRepository->create($validatedData);
    }

    public function updateAnnouncement(int $id, array $data): bool
    {
        $announcement = $this->announcementRepository->findById($id);

        if (!$announcement) {
            return false;
        }

        return $this->announcementRepository->update($announcement, $data);
    }

    public function deleteAnnouncement(int $id): bool
    {
        $announcement = $this->announcementRepository->findById($id);

        if (!$announcement) {
            return false;
        }

        return $this->announcementRepository->delete($announcement);
    }

    public function incrementViews(int $id): bool
    {
        $announcement = $this->announcementRepository->findById($id);

        if (!$announcement) {
            return false;
        }

        return $this->announcementRepository->incrementViews($announcement);
    }

    public function getStatusCounts(): array
    {
        return $this->announcementRepository->getStatusCounts();
    }

    public function getDisplayTypeCounts(): array
    {
        return $this->announcementRepository->getDisplayTypeCounts();
    }

    public function getActiveAnnouncements()
    {
        return $this->announcementRepository->getActiveAnnouncements();
    }

    public function getAnnouncementsForPublic(array $filters = []): array
    {
        $filters['active_only'] = true;
        return $this->announcementRepository->getAllPaginated(10, $filters)->items();
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
            Log::error('Error al obtener usuario por defecto', [
                'error' => $e->getMessage()
            ]);

            // En caso de error, usar un valor que pase la validación de la base de datos
            // Esto requiere que tengas al menos un usuario en la base de datos
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
                'last_name' => 'User',
                'full_name' => 'System User',
                'email' => 'system@incadev.com',
                'password' => bcrypt('temporary_password'),
                'role' => json_encode(['admin']),
                'status' => 'active',
            ]);

            Log::info('Usuario temporal creado para announcements', ['user_id' => $user->id]);

            return $user->id;
        } catch (\Exception $e) {
            Log::error('Error al crear usuario temporal', [
                'error' => $e->getMessage()
            ]);

            throw new \Exception('No se pudo crear un usuario temporal. Error: ' . $e->getMessage());
        }
    }

    public function getActiveCount(): int
    {
        return $this->announcementRepository->getActiveCount();
    }

    public function getTotalViews(): int
    {
        return $this->announcementRepository->getTotalViews();
    }

    public function getActiveAnnouncementsWithStats()
    {
        return $this->announcementRepository->getActiveAnnouncementsWithStats();
    }
}
