<?php

namespace App\Domains\DeveloperWeb\Services\ContentTypes;

use App\Domains\DeveloperWeb\Services\ContentService;
use App\Domains\DeveloperWeb\Repositories\ContentItemRepository;
use App\Domains\DeveloperWeb\Enums\ContentType;

class AnnouncementService extends ContentService
{
    public function __construct(ContentItemRepository $contentItemRepository)
    {
        parent::__construct($contentItemRepository, ContentType::ANNOUNCEMENT);
    }

    protected function prepareContentData(array $data): array
    {
        return [
            // Campos COMUNES
            'title' => $data['title'],
            'content' => $data['content'],
            'status' => $data['status'],
            
            // Campos EXCLUSIVOS de ANNOUNCEMENT
            'image_url' => $data['image_url'] ?? null,
            'start_date' => $this->formatDateTime($data['start_date']),
            'end_date' => $this->formatDateTime($data['end_date']),
            'priority' => $data['priority'] ?? 1,
            'item_type' => $data['item_type'] ?? 'banner',
            'target_page' => $data['target_page'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'button_text' => $data['button_text'] ?? null,
            
            // Metadata
            'metadata' => $this->prepareMetadata($data),
        ];
    }

    protected function prepareUpdateData(array $data): array
    {
        $updateData = [];

        // Campos que ANNOUNCEMENT puede actualizar
        $announcementFields = [
            'title', 'content', 'image_url', 'status', 'start_date', 'end_date',
            'priority', 'item_type', 'target_page', 'link_url', 'button_text'
        ];

        foreach ($announcementFields as $field) {
            if (array_key_exists($field, $data)) {
                if (in_array($field, ['start_date', 'end_date'])) {
                    $updateData[$field] = $this->formatDateTime($data[$field]);
                } else {
                    $updateData[$field] = $data[$field];
                }
            }
        }

        return $updateData;
    }

    // 📢 MÉTODOS ESPECÍFICOS SOLICITADOS
    
    public function getPublishedAnnouncements(int $perPage = 15)
    {
        // CORREGIDO: Usar el repository para obtener datos paginados
        return $this->contentItemRepository->getPublishedByType($this->contentType->value, $perPage);
    }

    public function resetViews(int $id): bool
    {
        $announcement = $this->contentItemRepository->findByIdAndType($id, $this->contentType->value);
        
        if (!$announcement) {
            return false;
        }

        return $this->contentItemRepository->resetViews($announcement);
    }
}