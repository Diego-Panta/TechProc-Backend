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

    public function getAllContactForms(int $perPage = 15): LengthAwarePaginator
    {
        return $this->contactFormRepository->getAllPaginated($perPage);
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

    public function respondToContact(int $id, string $response): bool
    {
        $contactForm = $this->contactFormRepository->findById($id);
        
        if (!$contactForm) {
            return false;
        }

        return $this->contactFormRepository->respondToContact($contactForm, $response);
    }

    public function getContactFormsByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->contactFormRepository->getByStatus($status, $perPage);
    }

    // Nuevo método para obtener estadísticas
    public function getContactStats(): array
    {
        return $this->contactFormRepository->getStats();
    }
}