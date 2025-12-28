<?php

namespace App\Domains\DeveloperWeb\Services\ContentTypes;

use App\Domains\DeveloperWeb\Services\ContentService;
use App\Domains\DeveloperWeb\Repositories\ContentItemRepository;
use App\Domains\DeveloperWeb\Enums\ContentType;
use App\Domains\DeveloperWeb\Enums\ContentStatus;
use App\Domains\DeveloperWeb\Enums\NewsItemType;
use App\Domains\DeveloperWeb\Enums\NewsCategory;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsService extends ContentService
{
    protected CloudinaryService $cloudinaryService;

    public function __construct(
        ContentItemRepository $contentItemRepository,
        CloudinaryService $cloudinaryService
    ) {
        parent::__construct($contentItemRepository, ContentType::NEWS);
        $this->cloudinaryService = $cloudinaryService;
    }

    protected function prepareContentData(array $data): array
    {
        // Generar slug si no se proporciona
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }

        // Manejar subida de imagen a Cloudinary si se envió un archivo
        $imageUrl = $data['image_url'] ?? null;

        if (isset($data['image_file']) && $data['image_file']) {
            try {
                // Generar nombre personalizado basado en el slug
                $customName = $data['slug'] ?? Str::slug($data['title']);

                // Subir imagen a Cloudinary
                $imageUrl = $this->cloudinaryService->uploadImage($data['image_file'], $customName);
            } catch (\Exception $e) {
                Log::error('Error al subir imagen a Cloudinary', [
                    'error' => $e->getMessage(),
                    'title' => $data['title']
                ]);
                // Si falla, mantener la URL anterior si existe
            }
        }

        return [
            // Campos COMUNES
            'title' => $data['title'],
            'content' => $data['content'],
            'status' => $data['status'],

            // Campos EXCLUSIVOS de NEWS
            'slug' => $data['slug'],
            'summary' => $data['summary'],
            'image_url' => $imageUrl,
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

        // Manejar actualización de imagen a Cloudinary si se envió un archivo nuevo
        if (isset($data['image_file']) && $data['image_file']) {
            try {
                // Obtener la URL de la imagen anterior para eliminarla
                $oldImageUrl = $data['_old_image_url'] ?? null;

                // Generar nombre personalizado basado en el slug
                $customName = $updateData['slug'] ?? $data['slug'] ?? Str::slug($data['title'] ?? 'news');

                // Actualizar imagen en Cloudinary (elimina la anterior y sube la nueva)
                $newImageUrl = $this->cloudinaryService->updateImage($oldImageUrl, $data['image_file'], $customName);

                $updateData['image_url'] = $newImageUrl;
            } catch (\Exception $e) {
                Log::error('Error al actualizar imagen en Cloudinary', [
                    'error' => $e->getMessage(),
                    'data' => $data
                ]);
                // Si falla, no actualizar la URL de la imagen
            }
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