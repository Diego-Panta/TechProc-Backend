<?php

namespace App\Domains\DeveloperWeb\Repositories;

use App\Domains\DeveloperWeb\Models\Announcement;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AnnouncementRepository
{
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Announcement::with('creator');

        // Aplicar filtros
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['target_page'])) {
            $query->where('target_page', $filters['target_page']);
        }

        if (!empty($filters['display_type'])) {
            $query->where('display_type', $filters['display_type']);
        }

        // Filtrar por fechas activas
        if (!empty($filters['active_only'])) {
            $now = now();
            $query->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->where('status', 'published');
        }

        return $query->orderBy('created_date', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Announcement
    {
        return Announcement::with('creator')->find($id);
    }

    public function create(array $data): Announcement
    {
        $id = DB::table('announcements')->insertGetId($data);
        return Announcement::find($id);
    }

    public function update(Announcement $announcement, array $data): bool
    {
        // Remover updated_at si existe en los datos
        if (array_key_exists('updated_at', $data)) {
            unset($data['updated_at']);
        }

        return $announcement->update($data);
    }

    public function delete(Announcement $announcement): bool
    {
        return $announcement->delete();
    }

    public function incrementViews(Announcement $announcement): bool
    {
        return $announcement->increment('views');
    }

    public function getNextAnnouncementId(): int
    {
        $lastAnnouncement = Announcement::orderBy('id_announcement', 'desc')->first();
        return $lastAnnouncement ? $lastAnnouncement->id_announcement + 1 : 1;
    }

    public function getStatusCounts(): array
    {
        return Announcement::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function getDisplayTypeCounts(): array
    {
        return Announcement::select('display_type', DB::raw('count(*) as count'))
            ->groupBy('display_type')
            ->pluck('count', 'display_type')
            ->toArray();
    }

    public function getActiveAnnouncements()
    {
        $now = now();
        return Announcement::with('creator')
            ->where('status', 'published')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->orderBy('created_date', 'desc')
            ->get(); // Quitar el ->toArray()
    }
}
