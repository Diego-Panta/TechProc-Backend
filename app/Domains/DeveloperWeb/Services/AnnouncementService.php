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

    public function createAnnouncement(array $data, int $userId): Announcement
    {
        // Usar el ID del usuario autenticado pasado desde el controlador
        $createdBy = $userId;

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
            'created_by' => $createdBy, // Usar el ID del usuario autenticado
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
