<?php

namespace App\Domains\DeveloperWeb\Repositories;

use App\Domains\DeveloperWeb\Models\ContactForm;
use App\Domains\DeveloperWeb\Enums\ContactFormStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ContactFormRepository
{
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ContactForm::with(['assignedTo.user']);

        // Aplicar filtros
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            // Validar que el estado sea válido
            if (ContactFormStatus::isValid($filters['status'])) {
                $query->where('status', $filters['status']);
            }
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['form_type'])) {
            $query->where('form_type', $filters['form_type']);
        }

        return $query->orderBy('submission_date', 'desc')
            ->paginate($perPage)
            ->appends($filters);
    }

    public function findById(int $id): ?ContactForm
    {
        return ContactForm::with(['assignedTo.user'])->find($id);
    }

    public function create(array $data): ContactForm
    {
        $id = DB::table('contact_forms')->insertGetId($data);
        return ContactForm::find($id);
    }

    public function update(ContactForm $contactForm, array $data): bool
    {
        return $contactForm->update($data);
    }

    public function delete(ContactForm $contactForm): bool
    {
        return $contactForm->delete();
    }

    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return ContactForm::with(['assignedTo.user'])
            ->where('status', $status)
            ->orderBy('submission_date', 'desc')
            ->paginate($perPage);
    }

    public function markAsSpam(ContactForm $contactForm): bool
    {
        return $contactForm->update(['status' => ContactFormStatus::SPAM->value]);
    }

    public function respondToContact(ContactForm $contactForm, string $response, ?int $assignedTo = null): bool
    {
        $updateData = [
            'response' => $response,
            'response_date' => now(),
            'status' => ContactFormStatus::RESPONDED->value
        ];

        if ($assignedTo) {
            $updateData['assigned_to'] = $assignedTo;
        }

        return $contactForm->update($updateData);
    }

    public function getNextContactId(): int
    {
        $lastContact = ContactForm::orderBy('id_contact', 'desc')->first();
        return $lastContact ? $lastContact->id_contact + 1 : 1;
    }

    public function getStats(): array
    {
        $stats = ContactForm::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Asegurarse de que todos los estados estén presentes
        $allStats = [];
        foreach (ContactFormStatus::values() as $status) {
            $allStats[$status] = $stats[$status] ?? 0;
        }

        return $allStats;
    }

    /**
     * Obtener contadores de estados para dashboard
     */
    public function getStatusCounts(): array
    {
        $counts = ContactForm::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Asegurar que todos los estados estén presentes
        $result = [];
        foreach (ContactFormStatus::values() as $status) {
            $result[$status] = $counts[$status] ?? 0;
        }

        $result['total'] = array_sum($result);
        $result['active'] = array_sum(array_intersect_key($result, array_flip(ContactFormStatus::getActiveStatuses())));

        return $result;
    }

    // Nuevos métodos para obtener opciones de filtros
    public function getFormTypes(): array
    {
        return ContactForm::distinct()
            ->whereNotNull('form_type')
            ->pluck('form_type')
            ->toArray();
    }

    public function getAssignedEmployees(): array
    {
        return ContactForm::with('assignedTo.user')
            ->whereNotNull('assigned_to')
            ->get()
            ->pluck('assignedTo.user.full_name', 'assigned_to')
            ->toArray();
    }

    /**
     * Obtener todos los contact forms para exportación (sin paginación)
     */
    public function getAllForExport(array $filters = [])
    {
        $query = ContactForm::with(['assignedTo.user']);

        // Aplicar filtros
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['form_type'])) {
            $query->where('form_type', $filters['form_type']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('submission_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('submission_date', '<=', $filters['end_date']);
        }

        return $query->orderBy('submission_date', 'desc')->get();
    }
}
