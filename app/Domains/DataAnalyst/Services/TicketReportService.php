<?php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Repositories\TicketReportRepository;

class TicketReportService
{
    protected $ticketReportRepository;

    public function __construct(TicketReportRepository $ticketReportRepository)
    {
        $this->ticketReportRepository = $ticketReportRepository;
    }

    public function getTicketReport(array $filters = [])
    {
        return $this->ticketReportRepository->getTicketsAnalysis($filters);
    }

    public function getTicketStatistics(array $filters = [])
    {
        return $this->ticketReportRepository->getTicketStatistics($filters);
    }

    public function getCategoryStatistics(array $filters = [])
    {
        return $this->ticketReportRepository->getCategoryStatistics($filters);
    }

    public function getTechnicianRanking(array $filters = [])
    {
        return $this->ticketReportRepository->getTechnicianRanking($filters);
    }
}