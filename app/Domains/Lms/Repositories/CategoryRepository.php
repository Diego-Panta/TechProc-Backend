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
}
