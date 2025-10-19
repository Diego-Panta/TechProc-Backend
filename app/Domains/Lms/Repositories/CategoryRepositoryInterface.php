<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function getAll(): Collection;
}
