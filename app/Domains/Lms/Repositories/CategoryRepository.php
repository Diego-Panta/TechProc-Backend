<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getAll(): Collection
    {
        return Category::withCount('courses')->orderBy('name')->get();
    }

    public function findById(int $id): ?Category
    {
        return Category::withCount('courses')->find($id);
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $category = Category::find($id);

        if (!$category) {
            return false;
        }

        return $category->update($data);
    }

    public function delete(int $id): bool
    {
        $category = Category::find($id);

        if (!$category) {
            return false;
        }

        return $category->delete();
    }
}
