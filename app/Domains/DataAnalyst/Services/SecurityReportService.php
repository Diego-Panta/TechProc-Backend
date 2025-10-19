<?php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Repositories\SecurityReportRepository;

class SecurityReportService
{
    protected $securityReportRepository;

    public function __construct(SecurityReportRepository $securityReportRepository)
    {
        $this->securityReportRepository = $securityReportRepository;
    }

    public function getSecurityReport(array $filters = [])
    {
        return [
            'analysis' => $this->securityReportRepository->getSecurityAnalysis($filters),
            'events' => $this->securityReportRepository->getSecurityEvents($filters),
            'alerts' => $this->securityReportRepository->getSecurityAlerts($filters)
        ];
    }

    public function getSecurityAnalysis(array $filters = [])
    {
        return $this->securityReportRepository->getSecurityAnalysis($filters);
    }

    public function getSecurityEvents(array $filters = [])
    {
        return $this->securityReportRepository->getSecurityEvents($filters);
    }

    public function getSecurityAlerts(array $filters = [])
    {
        return $this->securityReportRepository->getSecurityAlerts($filters);
    }
}