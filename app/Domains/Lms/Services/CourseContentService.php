<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\CourseContent;
use App\Domains\Lms\Repositories\CourseContentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CourseContentService
{
    protected CourseContentRepositoryInterface $repository;

    public function __construct(CourseContentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all course contents with filters and pagination
     */
    public function getAllContents(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    /**
     * Find a course content by ID
     */
    public function getContentById(int $contentId): ?CourseContent
    {
        return $this->repository->findById($contentId);
    }

    /**
     * Create a new course content
     */
    public function createContent(array $data): CourseContent
    {
        return $this->repository->create($data);
    }

    /**
     * Update an existing course content
     */
    public function updateContent(int $contentId, array $data): CourseContent
    {
        return $this->repository->update($contentId, $data);
    }

    /**
     * Delete a course content
     */
    public function deleteContent(int $contentId): bool
    {
        $content = $this->repository->findById($contentId);

        if (!$content) {
            return false;
        }

        return $this->repository->delete($contentId);
    }
}
