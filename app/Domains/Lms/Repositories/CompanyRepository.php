<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CompanyRepository implements CompanyRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Company::query();

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('industry', 'ILIKE', "%{$search}%")
                  ->orWhere('contact_name', 'ILIKE', "%{$search}%")
                  ->orWhere('contact_email', 'ILIKE', "%{$search}%");
            });
        }

        if (isset($filters['industry'])) {
            $query->where('industry', 'ILIKE', "%{$filters['industry']}%");
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(int $id): ?Company
    {
        return Company::find($id);
    }

    public function create(array $data): Company
    {
        return Company::create($data);
    }

    public function update(int $id, array $data): ?Company
    {
        $company = Company::find($id);

        if (!$company) {
            return null;
        }

        $company->update($data);

        return $company->fresh();
    }

    public function delete(int $id): bool
    {
        $company = Company::find($id);

        if (!$company) {
            return false;
        }

        return $company->delete();
    }
}
