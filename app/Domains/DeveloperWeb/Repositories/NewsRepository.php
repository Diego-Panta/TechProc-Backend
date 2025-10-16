<?php

namespace App\Domains\DeveloperWeb\Repositories;

use App\Domains\DeveloperWeb\Models\News;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class NewsRepository
{
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = News::with('author');

        // Aplicar filtros
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'ILIKE', '%' . $filters['search'] . '%')
                  ->orWhere('summary', 'ILIKE', '%' . $filters['search'] . '%');
        }

        // Filtrar por publicaciones activas
        if (!empty($filters['published_only'])) {
            $now = now();
            $query->where('status', 'published')
                  ->where('published_date', '<=', $now);
        }

        return $query->orderBy('created_date', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?News
    {
        return News::with('author')->find($id);
    }

    public function findBySlug(string $slug): ?News
    {
        return News::with('author')->where('slug', $slug)->first();
    }

    public function create(array $data): News
    {
        // Asegurar que los campos JSON se inserten correctamente
        if (isset($data['tags']) && is_array($data['tags'])) {
            $data['tags'] = json_encode($data['tags']);
        }

        $id = DB::table('news')->insertGetId($data);
        return News::find($id);
    }

    public function update(News $news, array $data): bool
    {
        // Actualizar updated_date automáticamente
        $data['updated_date'] = now();
        
        // Asegurar que los campos JSON se actualicen correctamente
        if (isset($data['tags']) && is_array($data['tags'])) {
            $data['tags'] = json_encode($data['tags']);
        }

        return $news->update($data);
    }

    public function delete(News $news): bool
    {
        return $news->delete();
    }

    public function incrementViews(News $news): bool
    {
        return $news->increment('views');
    }

    public function getNextNewsId(): int
    {
        $lastNews = News::orderBy('id_news', 'desc')->first();
        return $lastNews ? $lastNews->id_news + 1 : 1;
    }

    public function getStatusCounts(): array
    {
        return News::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function getCategoryCounts(): array
    {
        return News::select('category', DB::raw('count(*) as count'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();
    }

    public function getPublishedNews()
    {
        $now = now();
        return News::with('author')
            ->where('status', 'published')
            ->where('published_date', '<=', $now)
            ->orderBy('published_date', 'desc')
            ->get();
    }

    public function getRelatedNews(News $news, int $limit = 3)
    {
        return News::with('author')
            ->where('id', '!=', $news->id)
            ->where('category', $news->category)
            ->where('status', 'published')
            ->where('published_date', '<=', now())
            ->orderBy('published_date', 'desc')
            ->limit($limit)
            ->get();
    }
}