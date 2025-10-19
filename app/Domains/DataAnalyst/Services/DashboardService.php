<?php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Repositories\DashboardRepository;

class DashboardService
{
    protected $dashboardRepository;

    public function __construct(DashboardRepository $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    public function getDashboardData(array $filters = [])
    {
        return $this->dashboardRepository->getDashboardData($filters);
    }
}