<?php

namespace App\Domains\DeveloperWeb\Services\ContentTypes;

use App\Domains\DeveloperWeb\Services\ContentService;
use App\Domains\DeveloperWeb\Repositories\ContentItemRepository;
use App\Domains\DeveloperWeb\Enums\ContentType;
use App\Domains\DeveloperWeb\Enums\ContentStatus;
use App\Domains\DeveloperWeb\Enums\NewsItemType;
use App\Domains\DeveloperWeb\Enums\NewsCategory;
use Illuminate\Support\Str;

class NewsService extends ContentService
{
    public function __construct(ContentItemRepository $contentItemRepository)
    {
        parent::__construct($contentItemRepository, ContentType::NEWS);
    }

    protected function prepareContentData(array $data): array
    {
        // Generar slug si no se proporciona
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }

        return [
            // Campos COMUNES
            'title' => $data['title'],
            'content' => $data['content'],
            'status' => $data['status'],

            // Campos EXCLUSIVOS de NEWS
            'slug' => $data['slug'],
            'summary' => $data['summary'],
            'image_url' => $data['image_url'] ?? null,
            'published_date' => $this->formatDateTime($data['published_date'] ?? null) ??
                ($data['status'] === ContentStatus::PUBLISHED->value ? now()->format('Y-m-d H:i:s') : null),
            'category' => $data['category'], // Ahora validado por el enum NewsCategory
            'item_type' => $data['item_type'] ?? NewsItemType::ARTICLE->value,
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,

            // Metadata
            'metadata' => $this->prepareMetadata($data),
        ];
    }

    protected function prepareUpdateData(array $data): array
    {
        $updateData = [];

        // Campos que NEWS puede actualizar
        $newsFields = [
            'title',
            'slug',
            'content',
            'summary',
            'image_url',
            'status',
            'published_date',
            'category',
            'item_type',
            'seo_title',
            'seo_description'
        ];

        foreach ($newsFields as $field) {
            if (array_key_exists($field, $data)) {
                if ($field === 'published_date') {
                    $updateData[$field] = $this->formatDateTime($data[$field]);
                } else {
                    $updateData[$field] = $data[$field];
                }
            }
        }

        // Generar nuevo slug si el título cambió
        if (isset($data['title']) && (!isset($data['slug']) || empty($data['slug']))) {
            $updateData['slug'] = $this->generateUniqueSlug($data['title']);
        }

        // Actualizar metadata si hay tags
        if (isset($data['tags'])) {
            $updateData['metadata'] = $this->prepareMetadata($data);
        }

        return $updateData;
    }

    private function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    // MÉTODOS ESPECÍFICOS SOLICITADOS

    public function getPublishedNews(int $perPage = 15)
    {
        return $this->contentItemRepository->getPublishedByType($this->contentType->value, $perPage);
    }

    public function resetViews(int $id): bool
    {
        $news = $this->contentItemRepository->findByIdAndType($id, $this->contentType->value);

        if (!$news) {
            return false;
        }

        return $this->contentItemRepository->resetViews($news);
    }

    /**
     * Obtener categorías disponibles (ahora desde el enum)
     */
    public function getCategories(): array
    {
        return NewsCategory::forSelect();
    }

    public function getStats(): array
    {
        return $this->contentItemRepository->getNewsStats();
    }
}