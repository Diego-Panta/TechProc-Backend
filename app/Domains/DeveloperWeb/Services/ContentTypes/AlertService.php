<?php

namespace App\Domains\DeveloperWeb\Services\ContentTypes;

use App\Domains\DeveloperWeb\Services\ContentService;
use App\Domains\DeveloperWeb\Repositories\ContentItemRepository;
use App\Domains\DeveloperWeb\Enums\ContentType;

class AlertService extends ContentService
{
    public function __construct(ContentItemRepository $contentItemRepository)
    {
        parent::__construct($contentItemRepository, ContentType::ALERT);
    }

    protected function prepareContentData(array $data): array
    {
        return [
            // Campos COMUNES
            'title' => $data['title'],
            'content' => $data['content'],
            'status' => $data['status'],
            
            // Campos EXCLUSIVOS de ALERT
            'start_date' => $this->formatDateTime($data['start_date']),
            'end_date' => $this->formatDateTime($data['end_date']),
            'priority' => $data['priority'] ?? 1,
            'item_type' => $data['item_type'], // info, warning, error, success
            'link_url' => $data['link_url'] ?? null,
            'link_text' => $data['link_text'] ?? null,
            
            // Metadata (ALERT no usa views, image_url, etc.)
            'metadata' => $this->prepareMetadata($data),
        ];
    }

    protected function prepareUpdateData(array $data): array
    {
        $updateData = [];

        // Campos que ALERT puede actualizar
        $alertFields = [
            'title', 'content', 'status', 'start_date', 'end_date',
            'priority', 'item_type', 'link_url', 'link_text'
        ];

        foreach ($alertFields as $field) {
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

    // ⚠️ MÉTODOS ESPECÍFICOS SOLICITADOS
    
    public function getPublishedAlerts(int $perPage = 15)
    {
        // CORREGIDO: Usar el repository para obtener datos paginados
        return $this->contentItemRepository->getPublishedByType($this->contentType->value, $perPage);
    }
    
}