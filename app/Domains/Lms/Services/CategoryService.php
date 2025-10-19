<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\Category;
use App\Domains\Lms\Repositories\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    protected CategoryRepositoryInterface $repository;

    public function __construct(CategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllCategories(): Collection
    {
        return $this->repository->getAll();
    }

    public function getCategoryById(int $id): ?Category
    {
        return $this->repository->findById($id);
    }

    public function createCategory(array $data): Category
    {
        return $this->repository->create($data);
    }

    public function updateCategory(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteCategory(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
