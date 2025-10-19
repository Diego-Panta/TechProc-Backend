<?php

namespace App\Domains\Lms\Services;

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
}
