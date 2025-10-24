<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\Group;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GroupRepositoryInterface
{
    /**
     * Get all groups with filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Find a group by ID
     */
    public function findById(int $groupId): ?Group;

    /**
     * Create a new group
     */
    public function create(array $data): Group;

    /**
     * Update an existing group
     */
    public function update(int $groupId, array $data): Group;

    /**
     * Delete a group
     */
    public function delete(int $groupId): bool;
}
