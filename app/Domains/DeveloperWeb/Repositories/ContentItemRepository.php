<?php

namespace App\Domains\DeveloperWeb\Repositories;

use App\Domains\DeveloperWeb\Models\ContentItem;
use App\Domains\DeveloperWeb\Enums\ContentType;
use App\Domains\DeveloperWeb\Enums\ContentStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ContentItemRepository
{
    /**
     * Obtener todos los contenidos paginados con filtros
     */
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

    /**
     * Buscar contenido por ID y tipo
     */
    public function findByIdAndType(int $id, string $contentType): ?ContentItem
    {
        return ContentItem::ofType($contentType)->find($id);
    }

    /**
     * Buscar contenido por slug
     */
    public function findBySlug(string $slug): ?ContentItem
    {
        return ContentItem::where('slug', $slug)->first();
    }

    /**
     * Crear nuevo contenido
     */
    public function create(array $data): ContentItem
    {
        return ContentItem::create($data);
    }

    /**
     * Actualizar contenido existente
     */
    public function update(ContentItem $contentItem, array $data): bool
    {
        return $contentItem->update($data);
    }

    /**
     * Eliminar contenido
     */
    public function delete(ContentItem $contentItem): bool
    {
        return $contentItem->delete();
    }

    /**
     * Incrementar vistas de un contenido
     */
    public function incrementViews(ContentItem $contentItem): bool
    {
        return $contentItem->increment('views');
    }

    /**
     * Resetear vistas a 0
     */
    public function resetViews(ContentItem $contentItem): bool
    {
        return $contentItem->update(['views' => 0]);
    }

    /**
     * Verificar si un slug ya existe para un tipo de contenido
     */
    public function slugExists(string $slug, string $contentType, int $excludeId = null): bool
    {
        $query = ContentItem::ofType($contentType)->where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Obtener conteo de contenidos por estado
     */
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

    /**
     * Obtener conteo de contenidos por categoría (solo para NEWS)
     */
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

    /**
     * Obtener contenido activo por tipo (sin paginación)
     */
    public function getActiveContent(string $contentType)
    {
        return ContentItem::ofType($contentType)
            ->shouldBeDisplayed()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtener noticias por categoría (solo para NEWS)
     */
    public function getByCategory(string $category, string $contentType, int $perPage = 10)
    {
        return ContentItem::ofType($contentType)
            ->where('category', $category)
            ->shouldBeDisplayed()
            ->orderBy('published_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtener contenido destacado (solo para NEWS)
     */
    public function getFeaturedContent(string $contentType)
    {
        return ContentItem::ofType($contentType)
            ->where('status', ContentStatus::PUBLISHED->value)
            ->whereJsonContains('metadata->featured', true)
            ->orderBy('published_date', 'desc')
            ->get();
    }

    /**
     * Obtener estadísticas generales por tipo de contenido
     */
    public function getStatsByType(string $contentType): array
    {
        $total = ContentItem::ofType($contentType)->count();
        $published = ContentItem::ofType($contentType)
            ->shouldBeDisplayed()
            ->count();
        
        $statusCounts = $this->getStatusCounts($contentType);
        $totalViews = ContentItem::ofType($contentType)->sum('views');

        return [
            'total' => $total,
            'published' => $published,
            'status_counts' => $statusCounts,
            'total_views' => $totalViews,
        ];
    }

    /**
     * Obtener estadísticas específicas para NEWS
     */
    public function getNewsStats(): array
    {
        $baseStats = $this->getStatsByType(ContentType::NEWS->value);
        
        // Estadísticas adicionales para NEWS
        $categoryCounts = $this->getCategoryCounts(ContentType::NEWS->value);
        $recentPublished = ContentItem::news()
            ->shouldBeDisplayed()
            ->orderBy('published_date', 'desc')
            ->limit(5)
            ->count();

        return array_merge($baseStats, [
            'categories_count' => count($categoryCounts),
            'recent_published' => $recentPublished,
            'most_viewed' => ContentItem::news()
                ->orderBy('views', 'desc')
                ->limit(1)
                ->first(['title', 'views']),
        ]);
    }

    /**
     * Obtener estadísticas específicas para ANNOUNCEMENT
     */
    public function getAnnouncementStats(): array
    {
        $baseStats = $this->getStatsByType(ContentType::ANNOUNCEMENT->value);
        
        // Estadísticas adicionales para ANNOUNCEMENT
        $activeNow = ContentItem::announcements()
            ->shouldBeDisplayed()
            ->count();

        $highPriority = ContentItem::announcements()
            ->where('priority', '>=', 7)
            ->shouldBeDisplayed()
            ->count();

        $byType = ContentItem::announcements()
            ->select('item_type', DB::raw('count(*) as count'))
            ->groupBy('item_type')
            ->pluck('count', 'item_type')
            ->toArray();

        return array_merge($baseStats, [
            'active_now' => $activeNow,
            'high_priority' => $highPriority,
            'by_type' => $byType,
        ]);
    }

    /**
     * Obtener estadísticas específicas para ALERT
     */
    public function getAlertStats(): array
    {
        $baseStats = $this->getStatsByType(ContentType::ALERT->value);
        
        // Estadísticas adicionales para ALERT
        $activeNow = ContentItem::alerts()
            ->shouldBeDisplayed()
            ->count();

        $highPriority = ContentItem::alerts()
            ->where('priority', '>=', 7)
            ->shouldBeDisplayed()
            ->count();

        $byType = ContentItem::alerts()
            ->select('item_type', DB::raw('count(*) as count'))
            ->groupBy('item_type')
            ->pluck('count', 'item_type')
            ->toArray();

        $expiringSoon = ContentItem::alerts()
            ->where('end_date', '<=', now()->addDays(3))
            ->where('end_date', '>=', now())
            ->shouldBeDisplayed()
            ->count();

        return array_merge($baseStats, [
            'active_now' => $activeNow,
            'high_priority' => $highPriority,
            'by_type' => $byType,
            'expiring_soon' => $expiringSoon,
        ]);
    }

    /**
     * Obtener estadísticas generales de todos los tipos
     */
    public function getOverallStats(): array
    {
        $newsStats = $this->getStatsByType(ContentType::NEWS->value);
        $announcementStats = $this->getStatsByType(ContentType::ANNOUNCEMENT->value);
        $alertStats = $this->getStatsByType(ContentType::ALERT->value);

        return [
            'news' => $newsStats,
            'announcements' => $announcementStats,
            'alerts' => $alertStats,
            'total_content' => $newsStats['total'] + $announcementStats['total'] + $alertStats['total'],
            'total_published' => $newsStats['published'] + $announcementStats['published'] + $alertStats['published'],
            'total_views' => $newsStats['total_views'] + $announcementStats['total_views'],
        ];
    }
}