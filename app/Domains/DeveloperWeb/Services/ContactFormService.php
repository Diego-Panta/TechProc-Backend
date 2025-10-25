<?php

namespace App\Domains\DeveloperWeb\Services;

use App\Domains\DeveloperWeb\Models\ContactForm;
use App\Domains\DeveloperWeb\Repositories\ContactFormRepository;
use App\Domains\DeveloperWeb\Enums\ContactFormStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Domains\DeveloperWeb\Mail\ContactFormResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ContactFormService
{
    private string $brevoApiKey;
    private string $brevoSenderEmail;
    private string $brevoSenderName;
    private bool $useGmailAsPrimary;

    public function __construct(
        private ContactFormRepository $contactFormRepository
    ) {
        $this->brevoApiKey = env('BREVO_API_KEY');
        $this->brevoSenderEmail = env('BREVO_SENDER_EMAIL');
        $this->brevoSenderName = env('BREVO_SENDER_NAME');
        $this->useGmailAsPrimary = env('USE_GMAIL_AS_PRIMARY');
    }

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
            'status' => ContactFormStatus::PENDING->value, // Usar enum
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

        // 1. Primero actualizar en base de datos
        $success = $this->contactFormRepository->respondToContact($contactForm, $response, $assignedTo);

        if ($success) {
            // 2. Enviar email de respuesta con sistema de fallback
            $this->sendResponseEmailWithFallback($contactForm, $response);
        }

        return $success;
    }

    /**
     * Enviar email con sistema de fallback (Gmail → Brevo)
     */
    private function sendResponseEmailWithFallback(ContactForm $contactForm, string $response): void
    {
        $emailSent = false;

        // Opción 1: Intentar con Gmail primero (si está configurado como primario)
        if ($this->useGmailAsPrimary && $this->isGmailConfigured()) {
            $emailSent = $this->sendResponseEmailGmail($contactForm, $response);

            if ($emailSent) {
                Log::info('Email enviado exitosamente via Gmail', [
                    'contact_form_id' => $contactForm->id,
                    'user_email' => $contactForm->email,
                    'provider' => 'gmail'
                ]);
                return;
            } else {
                Log::warning('Fallback a Brevo: Gmail falló', [
                    'contact_form_id' => $contactForm->id,
                    'user_email' => $contactForm->email
                ]);
            }
        }
        // Opción 2: Usar Brevo API (fallback o primario)
        if (!$emailSent) {
            $emailSent = $this->sendResponseEmailViaBrevo($contactForm, $response);

            if ($emailSent) {
                Log::info('Email enviado exitosamente via Brevo (fallback)', [
                    'contact_form_id' => $contactForm->id,
                    'user_email' => $contactForm->email,
                    'provider' => 'brevo_fallback'
                ]);
            } else {
                Log::error('Ambos métodos de email fallaron', [
                    'contact_form_id' => $contactForm->id,
                    'user_email' => $contactForm->email,
                    'providers_tried' => $this->useGmailAsPrimary ? ['gmail', 'brevo'] : ['brevo']
                ]);
            }
        }
    }

    /**
     * Enviar email de respuesta usando Brevo API
     */
    private function sendResponseEmailViaBrevo(ContactForm $contactForm, string $response): bool
    {
        try {
            $emailData = [
                'sender' => [
                    'name' => $this->brevoSenderName,
                    'email' => $this->brevoSenderEmail,
                ],
                'to' => [
                    [
                        'email' => $contactForm->email,
                        'name' => $contactForm->full_name,
                    ]
                ],
                'subject' => "Respuesta a tu consulta: {$contactForm->subject}",
                'htmlContent' => $this->buildEmailTemplate($contactForm, $response),
                'replyTo' => [
                    'email' => $this->brevoSenderEmail,
                    'name' => $this->brevoSenderName,
                ]
            ];

            $response = Http::withHeaders([
                'api-key' => $this->brevoApiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post('https://api.brevo.com/v3/smtp/email', $emailData);

            if ($response->successful()) {
                Log::info('Email enviado correctamente via Brevo API', [
                    'contact_form_id' => $contactForm->id,
                    'user_email' => $contactForm->email,
                    'brevo_response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Error enviando email via Brevo API', [
                    'contact_form_id' => $contactForm->id,
                    'user_email' => $contactForm->email,
                    'status_code' => $response->status(),
                    'error' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Excepción enviando email via Brevo API', [
                'contact_form_id' => $contactForm->id,
                'user_email' => $contactForm->email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Construir template del email
     */
    private function buildEmailTemplate(ContactForm $contactForm, string $response): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #667eea; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; border: 1px solid #ddd; }
                .response { background: white; border-left: 4px solid #4CAF50; padding: 15px; margin: 15px 0; }
                .footer { text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>¡Hola {$contactForm->full_name}!</h1>
                <p>Hemos respondido a tu consulta</p>
            </div>

            <div class='content'>
                <p>Gracias por contactarnos. Aquí está la respuesta a tu consulta <strong>\"{$contactForm->subject}\"</strong>:</p>

                <div class='response'>
                    " . nl2br(htmlspecialchars($response)) . "
                </div>

                <p>Si tienes más preguntas, no dudes en respondernos a este correo.</p>

                <div class='footer'>
                    <p><strong>Fecha de respuesta:</strong> " . now()->format('d/m/Y H:i') . "</p>
                    <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
                    <p>&copy; " . date('Y') . " " . config('app.name', 'Tu App') . "</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Enviar email de respuesta al usuario
     */
    private function sendResponseEmailGmail(ContactForm $contactForm, string $response): void
    {
        try {
            Mail::to($contactForm->email)
                ->send(new ContactFormResponse(
                    fullName: $contactForm->full_name,
                    originalSubject: $contactForm->subject,
                    response: $response
                ));

            Log::info('Email de respuesta enviado correctamente', [
                'contact_form_id' => $contactForm->id,
                'user_email' => $contactForm->email,
                'subject' => $contactForm->subject
            ]);
        } catch (\Exception $e) {
            Log::error('Error al enviar email de respuesta', [
                'contact_form_id' => $contactForm->id,
                'user_email' => $contactForm->email,
                'error' => $e->getMessage()
            ]);

            // No lanzamos excepción para no interrumpir el flujo principal
            // La respuesta ya se guardó en la base de datos
        }
    }

    private function isGmailConfigured(): bool
    {
        $gmailUser = env('MAIL_USERNAME');
        $gmailPass = env('MAIL_PASSWORD');
        
        return !empty($gmailUser) && !empty($gmailPass) && 
               env('MAIL_MAILER') === 'smtp' && 
               env('MAIL_HOST') === 'smtp.gmail.com';
    }

    /**
     * Obtener formularios por estado con validación
     */
    public function getContactFormsByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        if (!ContactFormStatus::isValid($status)) {
            throw new \InvalidArgumentException("Estado no válido: {$status}");
        }

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

        // Validar que el estado sea válido
        if (!ContactFormStatus::isValid($status)) {
            throw new \InvalidArgumentException("Estado no válido: {$status}");
        }

        return $this->contactFormRepository->update($contactForm, [
            'status' => $status
        ]);
    }

    /**
     * Exportar formularios de contacto para CSV
     */
    /*public function exportContactForms(array $filters = []): array
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
    }*/

    /**
     * Obtener estadísticas mejoradas
     */
    public function getEnhancedStats(): array
    {
        $counts = $this->contactFormRepository->getStatusCounts();

        // Calcular porcentajes
        $total = $counts['total'];
        $percentages = [];

        foreach (ContactFormStatus::values() as $status) {
            $percentages[$status] = $total > 0 ? round(($counts[$status] / $total) * 100, 1) : 0;
        }

        return [
            'counts' => $counts,
            'percentages' => $percentages,
            'status_labels' => ContactFormStatus::labels(),
        ];
    }
}
