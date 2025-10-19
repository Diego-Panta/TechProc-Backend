<?php

namespace App\Domains\DeveloperWeb\Services;

use App\Domains\DeveloperWeb\Models\ContactForm;
use App\Domains\DeveloperWeb\Repositories\ContactFormRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ContactFormService
{
    public function __construct(
        private ContactFormRepository $contactFormRepository
    ) {}

    public function getAllContactForms(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->contactFormRepository->getAllPaginated($filters, $perPage);
    }

    public function getContactFormById(int $id): ?ContactForm
    {
        return $this->contactFormRepository->findById($id);
    }

    public function createContactForm(array $data): ContactForm
    {
        $validatedData = [
            'id_contact' => $this->contactFormRepository->getNextContactId(),
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'form_type' => $data['form_type'] ?? 'general',
            'status' => 'pending',
            'submission_date' => now(),
        ];

        return $this->contactFormRepository->create($validatedData);
    }

    public function markAsSpam(int $id): bool
    {
        $contactForm = $this->contactFormRepository->findById($id);

        if (!$contactForm) {
            return false;
        }

        return $this->contactFormRepository->markAsSpam($contactForm);
    }

    public function respondToContact(int $id, string $response, ?int $assignedTo = null): bool
    {
        $contactForm = $this->contactFormRepository->findById($id);

        if (!$contactForm) {
            return false;
        }

        return $this->contactFormRepository->respondToContact($contactForm, $response, $assignedTo);
    }

    public function getContactFormsByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->contactFormRepository->getByStatus($status, $perPage);
    }

    public function getContactStats(): array
    {
        return $this->contactFormRepository->getStats();
    }

    // Nuevos métodos para filtros
    public function getFormTypes(): array
    {
        return $this->contactFormRepository->getFormTypes();
    }

    public function getAssignedEmployees(): array
    {
        return $this->contactFormRepository->getAssignedEmployees();
    }

    /**
     * Actualizar asignación de formulario de contacto
     */
    public function updateContactFormAssignment(int $id, int $employeeId): bool
    {
        $contactForm = $this->contactFormRepository->findById($id);

        if (!$contactForm) {
            return false;
        }

        return $this->contactFormRepository->update($contactForm, [
            'assigned_to' => $employeeId,
            'status' => 'in_progress'
        ]);
    }

    /**
     * Actualizar estado del formulario de contacto
     */
    public function updateContactFormStatus(int $id, string $status): bool
    {
        $contactForm = $this->contactFormRepository->findById($id);

        if (!$contactForm) {
            return false;
        }

        return $this->contactFormRepository->update($contactForm, [
            'status' => $status
        ]);
    }

    /**
     * Exportar formularios de contacto para CSV
     */
    public function exportContactForms(array $filters = []): array
    {
        $contactForms = $this->contactFormRepository->getAllForExport($filters);

        return $contactForms->map(function ($contact) {
            return [
                'id' => $contact->id,
                'id_contact' => $contact->id_contact,
                'full_name' => $contact->full_name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'company' => $contact->company,
                'subject' => $contact->subject,
                'form_type' => $contact->form_type,
                'status' => $contact->status,
                'submission_date' => $contact->submission_date->format('Y-m-d H:i:s'),
                'response_date' => $contact->response_date ? $contact->response_date->format('Y-m-d H:i:s') : '',
                'assigned_to' => $contact->assignedTo ? $contact->assignedTo->user->full_name : 'No asignado'
            ];
        })->toArray();
    }
}
