<?php

namespace App\Domains\DeveloperWeb\Repositories;

use App\Domains\DeveloperWeb\Models\ContactForm;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ContactFormRepository
{
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return ContactForm::with(['assignedTo.user'])
            ->orderBy('submission_date', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?ContactForm
    {
        return ContactForm::with(['assignedTo.user'])->find($id);
    }

    public function create(array $data): ContactForm
    {
        // Usar Query Builder para evitar conflictos con timestamps
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
        return $contactForm->update(['status' => 'spam']);
    }

    public function respondToContact(ContactForm $contactForm, string $response): bool
    {
        return $contactForm->update([
            'response' => $response,
            'response_date' => now(),
            'status' => 'responded'
        ]);
    }

    public function getNextContactId(): int
    {
        $lastContact = ContactForm::orderBy('id_contact', 'desc')->first();
        return $lastContact ? $lastContact->id_contact + 1 : 1;
    }

    // Nuevo método para obtener estadísticas
    public function getStats(): array
    {
        return ContactForm::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }
}