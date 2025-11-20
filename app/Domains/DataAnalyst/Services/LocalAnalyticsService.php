<?php
// app/Domains/DataAnalyst/Services/LocalAnalyticsService.php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Repositories\LocalAnalyticsRepository;
use Illuminate\Support\Collection;

class LocalAnalyticsService
{
    protected LocalAnalyticsRepository $repository;

    public function __construct(LocalAnalyticsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Obtener estudiantes activos
     */
    public function getActiveStudents(array $filters = []): Collection
    {
        return $this->repository->getActiveStudents($filters);
    }

    /**
     * Obtener grupos con docentes
     */
    public function getGroupsWithTeachers(array $filters = []): Collection
    {
        return $this->repository->getGroupsWithTeachers($filters);
    }

    /**
     * Obtener grupos con estudiantes
     */
    public function getGroupsWithStudents(array $filters = []): Collection
    {
        return $this->repository->getGroupsWithStudents($filters);
    }

    /**
     * Resumen de asistencia
     */
    public function getAttendanceSummary(array $filters = []): Collection
    {
        return $this->repository->getAttendanceSummary($filters);
    }

    /**
     * Resumen de calificaciones
     */
    public function getGradesSummary(array $filters = []): Collection
    {
        return $this->repository->getGradesSummary($filters);
    }

    /**
     * Resumen de pagos
     */
    public function getPaymentsSummary(array $filters = []): Collection
    {
        return $this->repository->getPaymentsSummary($filters);
    }

    /**
     * Tickets de soporte
     */
    public function getSupportTickets(array $filters = []): Collection
    {
        return $this->repository->getSupportTickets($filters);
    }

    /**
     * Citas programadas
     */
    public function getAppointments(array $filters = []): Collection
    {
        return $this->repository->getAppointments($filters);
    }

    /**
     * Dashboard rápido
     */
    public function getQuickDashboard(): array
    {
        return $this->repository->getQuickDashboard();
    }

    /**
     * Métricas combinadas para reporting
     */
    public function getCombinedReport(array $filters = []): array
    {
        return [
            'dashboard' => $this->getQuickDashboard(),
            'active_students' => $this->getActiveStudents($filters)->take(10),
            'recent_payments' => $this->getPaymentsSummary($filters)->take(10),
            'open_tickets' => $this->getSupportTickets(['status' => 'open'])->take(10),
        ];
    }
}