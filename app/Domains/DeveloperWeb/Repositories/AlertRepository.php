<?php

namespace App\Domains\DeveloperWeb\Repositories;

use App\Domains\DeveloperWeb\Models\Alert;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AlertRepository
{
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Alert::with('creator');

        // Aplicar filtros
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filtrar por fechas activas
        if (!empty($filters['active_only'])) {
            $now = now();
            $query->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->where('status', 'active');
        }

        // Filtrar por prioridad
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query->orderBy('priority', 'asc')
            ->orderBy('created_date', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Alert
    {
        return Alert::with('creator')->find($id);
    }

    public function create(array $data): Alert
    {
        $id = DB::table('alerts')->insertGetId($data);
        return Alert::find($id);
    }

    public function update(Alert $alert, array $data): bool
    {
        // Remover updated_at si existe en los datos
        if (array_key_exists('updated_at', $data)) {
            unset($data['updated_at']);
        }

        return $alert->update($data);
    }

    public function delete(Alert $alert): bool
    {
        return $alert->delete();
    }

    public function getNextAlertId(): int
    {
        $lastAlert = Alert::orderBy('id_alert', 'desc')->first();
        return $lastAlert ? $lastAlert->id_alert + 1 : 1;
    }

    public function getStatusCounts(): array
    {
        return Alert::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function getTypeCounts(): array
    {
        return Alert::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    public function getPriorityCounts(): array
    {
        return Alert::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();
    }

    public function getActiveAlerts()
    {
        $now = now();
        return Alert::with('creator')
            ->where('status', 'active')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->orderBy('priority', 'asc')
            ->orderBy('created_date', 'desc')
            ->get();
    }

    public function getHighPriorityAlerts()
    {
        $now = now();
        return Alert::with('creator')
            ->where('status', 'active')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where('priority', '>=', 3) // Prioridad alta = 3, crítica = 4, emergencia = 5
            ->orderBy('priority', 'desc')
            ->orderBy('created_date', 'desc')
            ->get();
    }
}