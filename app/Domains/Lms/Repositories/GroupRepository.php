<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\Group;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GroupRepository implements GroupRepositoryInterface
{
    /**
     * Get all groups with filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Group::with('course');

        // Filtro por course_id
        if (isset($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        // Filtro por status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Búsqueda por código o nombre
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('code', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('name', 'LIKE', "%{$filters['search']}%");
            });
        }

        // Filtro por rango de fechas
        if (isset($filters['start_date_from'])) {
            $query->where('start_date', '>=', $filters['start_date_from']);
        }

        if (isset($filters['start_date_to'])) {
            $query->where('start_date', '<=', $filters['start_date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Find a group by ID
     */
    public function findById(int $groupId): ?Group
    {
        return Group::with('course')->find($groupId);
    }

    /**
     * Create a new group
     */
    public function create(array $data): Group
    {
        return Group::create($data);
    }

    /**
     * Update an existing group
     */
    public function update(int $groupId, array $data): Group
    {
        $group = Group::findOrFail($groupId);
        $group->update($data);

        return $group->fresh();
    }

    /**
     * Delete a group
     */
    public function delete(int $groupId): bool
    {
        $group = Group::findOrFail($groupId);
        return $group->delete();
    }
}
