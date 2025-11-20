<?php

namespace App\Domains\DeveloperWeb\Services;

use App\Domains\DeveloperWeb\Models\ContentItem;
use App\Domains\DeveloperWeb\Repositories\ContentItemRepository;
use App\Domains\DeveloperWeb\Enums\ContentType;
use App\Domains\DeveloperWeb\Enums\ContentStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

abstract class ContentService
{
    public function __construct(
        protected ContentItemRepository $contentItemRepository,
        protected ContentType $contentType
    ) {}

    abstract protected function prepareContentData(array $data): array;
    abstract protected function prepareUpdateData(array $data): array;

    public function getAll(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $filters['content_type'] = $this->contentType->value;
        return $this->contentItemRepository->getAllPaginated($perPage, $filters);
    }

    public function getById(int $id): ?ContentItem
    {
        return $this->contentItemRepository->findByIdAndType($id, $this->contentType->value);
    }

    public function create(array $data): ContentItem
    {
        $contentData = $this->prepareContentData($data);
        $contentData['content_type'] = $this->contentType->value;
        
        return $this->contentItemRepository->create($contentData);
    }

    public function update(int $id, array $data): bool
    {
        $content = $this->contentItemRepository->findByIdAndType($id, $this->contentType->value);
        
        if (!$content) {
            return false;
        }

        $updateData = $this->prepareUpdateData($data);
        return $this->contentItemRepository->update($content, $updateData);
    }

    public function delete(int $id): bool
    {
        $content = $this->contentItemRepository->findByIdAndType($id, $this->contentType->value);
        
        if (!$content) {
            return false;
        }

        return $this->contentItemRepository->delete($content);
    }

    public function incrementViews(int $id): bool
    {
        $content = $this->contentItemRepository->findByIdAndType($id, $this->contentType->value);
        
        if (!$content) {
            return false;
        }

        return $this->contentItemRepository->incrementViews($content);
    }

    public function getStatusCounts(): array
    {
        return $this->contentItemRepository->getStatusCounts($this->contentType->value);
    }

    public function getActiveContent()
    {
        return $this->contentItemRepository->getActiveContent($this->contentType->value);
    }

    protected function formatDateTime($date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Log::warning('Error parsing date', [
                'date' => $date,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    protected function prepareMetadata(array $data, array $currentMetadata = []): array
    {
        $metadata = $currentMetadata;

        if (isset($data['tags'])) {
            $tags = is_string($data['tags']) ? 
                json_decode($data['tags'], true) ?? [] : 
                $data['tags'];
            
            if (is_array($tags)) {
                $tags = array_filter($tags, function ($tag) {
                    return is_string($tag) && !empty(trim($tag));
                });
                $metadata['tags'] = array_values($tags);
            }
        }

        // Tiempo de lectura para noticias
        if (isset($data['content']) && $this->contentType === ContentType::NEWS) {
            $wordCount = str_word_count(strip_tags($data['content']));
            $readTime = ceil($wordCount / 200);
            $metadata['read_time'] = max(1, $readTime) . ' min';
        }

        // Configuraciones adicionales
        $metadata['allow_comments'] = $data['allow_comments'] ?? true;
        $metadata['featured'] = $data['featured'] ?? false;

        return $metadata;
    }

    // Método protegido para verificar slug
    protected function slugExists(string $slug, int $excludeId = null): bool
    {
        return $this->contentItemRepository->slugExists($slug, $this->contentType->value, $excludeId);
    }
}