<?php

namespace App\Domains\DeveloperWeb\Repositories;

use App\Domains\DeveloperWeb\Models\ContentItem;
use App\Domains\DeveloperWeb\Enums\ContentType;
use App\Domains\DeveloperWeb\Enums\ContentStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ContentItemRepository
{
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = ContentItem::query();

        // Filtrar por tipo de contenido si se especifica
        if (!empty($filters['content_type'])) {
            $query->ofType($filters['content_type']);
        }

        // Aplicar filtros
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'ILIKE', '%' . $filters['search'] . '%')
                  ->orWhere('summary', 'ILIKE', '%' . $filters['search'] . '%')
                  ->orWhere('content', 'ILIKE', '%' . $filters['search'] . '%');
            });
        }

        // Filtrar por publicaciones activas
        if (!empty($filters['published_only'])) {
            $query->shouldBeDisplayed();
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtener contenido publicado por tipo (con paginación)
     */
    public function getPublishedByType(string $contentType, int $perPage = 15): LengthAwarePaginator
    {
        return ContentItem::ofType($contentType)
            ->shouldBeDisplayed()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?ContentItem
    {
        return ContentItem::find($id);
    }

    public function findByIdAndType(int $id, string $contentType): ?ContentItem
    {
        return ContentItem::ofType($contentType)->find($id);
    }

    public function findBySlug(string $slug): ?ContentItem
    {
        return ContentItem::where('slug', $slug)->first();
    }

    public function create(array $data): ContentItem
    {
        return ContentItem::create($data);
    }

    public function update(ContentItem $contentItem, array $data): bool
    {
        return $contentItem->update($data);
    }

    public function delete(ContentItem $contentItem): bool
    {
        return $contentItem->delete();
    }

    public function incrementViews(ContentItem $contentItem): bool
    {
        return $contentItem->increment('views');
    }

    public function resetViews(ContentItem $contentItem): bool
    {
        return $contentItem->update(['views' => 0]);
    }

    public function slugExists(string $slug, string $contentType, int $excludeId = null): bool
    {
        $query = ContentItem::ofType($contentType)->where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getStatusCounts(string $contentType = null): array
    {
        $query = ContentItem::query();
        
        if ($contentType) {
            $query->ofType($contentType);
        }

        return $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function getCategoryCounts(string $contentType = null): array
    {
        $query = ContentItem::query();
        
        if ($contentType) {
            $query->ofType($contentType);
        }

        return $query->select('category', DB::raw('count(*) as count'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();
    }

    public function getPublishedNews()
    {
        return ContentItem::news()
            ->shouldBeDisplayed()
            ->orderBy('published_date', 'desc')
            ->get();
    }

    public function getActiveContent(string $contentType)
    {
        return ContentItem::ofType($contentType)
            ->shouldBeDisplayed()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getActiveByType(string $contentType)
    {
        return $this->getActiveContent($contentType);
    }

    public function getRelatedNews(ContentItem $contentItem, int $limit = 3)
    {
        return ContentItem::news()
            ->where('id', '!=', $contentItem->id)
            ->where('category', $contentItem->category)
            ->shouldBeDisplayed()
            ->orderBy('published_date', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getTotalViews(): int
    {
        return ContentItem::sum('views');
    }

    public function getRecentNews(int $limit = 5)
    {
        return ContentItem::news()
            ->shouldBeDisplayed()
            ->orderBy('published_date', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getByCategory(string $category, string $contentType, int $perPage = 10)
    {
        return ContentItem::ofType($contentType)
            ->where('category', $category)
            ->shouldBeDisplayed()
            ->orderBy('published_date', 'desc')
            ->paginate($perPage);
    }

    public function getFeaturedContent(string $contentType)
    {
        return ContentItem::ofType($contentType)
            ->where('status', ContentStatus::PUBLISHED->value)
            ->whereJsonContains('metadata->featured', true)
            ->orderBy('published_date', 'desc')
            ->get();
    }

    public function getByTargetPage(string $targetPage, string $contentType)
    {
        return ContentItem::ofType($contentType)
            ->where('target_page', $targetPage)
            ->shouldBeDisplayed()
            ->orderBy('priority', 'desc')
            ->get();
    }

    public function getHighPriorityContent(string $contentType, int $priority = 7)
    {
        return ContentItem::ofType($contentType)
            ->where('priority', '>=', $priority)
            ->shouldBeDisplayed()
            ->orderBy('priority', 'desc')
            ->get();
    }

    public function getContentByDateRange(string $contentType, string $startDate, string $endDate)
    {
        return ContentItem::ofType($contentType)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->shouldBeDisplayed()
            ->get();
    }
}