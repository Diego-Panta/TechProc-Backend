<?php

namespace App\Domains\DeveloperWeb\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ContactFormService
{
    private ?string $brevoApiKey;
    private ?string $brevoSenderEmail;
    private ?string $brevoSenderName;
    private bool $useGmailAsPrimary;

    public function __construct()
    {
        $this->brevoApiKey = env('BREVO_API_KEY');
        $this->brevoSenderEmail = env('BREVO_SENDER_EMAIL');
        $this->brevoSenderName = env('BREVO_SENDER_NAME', 'Incadev');
        $this->useGmailAsPrimary = env('USE_GMAIL_AS_PRIMARY', true);
    }

    /**
     * Procesar formulario de contacto - Solo envía email, no guarda en DB
     */
    public function processContactForm(array $data): array
    {
        try {
            // Solo enviar notificación al administrador
            $adminEmailSent = $this->sendNotificationToAdmin($data);

            // Y confirmación al usuario
            $userEmailSent = $this->sendConfirmationToUser($data);

            return [
                'success' => true,
                'message' => '¡Gracias por contactarnos! Te responderemos pronto.',
                'data' => [
                    'notification_sent' => $adminEmailSent,
                    'confirmation_sent' => $userEmailSent
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al procesar el formulario.'
            ];
        }
    }

    /**
     * Responder a formulario de contacto - Solo envía email de respuesta
     */
    public function respondToContact(array $contactData, string $response): array
    {
        try {
            Log::info('📧 Respondiendo a contacto', [
                'user_email' => $contactData['email'],
                'subject' => $contactData['subject']
            ]);

            $emailSent = $this->sendResponseEmailWithFallback($contactData, $response);

            if ($emailSent) {
                return [
                    'success' => true,
                    'message' => 'Respuesta enviada correctamente al usuario.',
                    'data' => [
                        'email_sent' => true,
                        'user_email' => $contactData['email']
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudo enviar la respuesta por email.',
                    'data' => [
                        'email_sent' => false
                    ]
                ];
            }
        } catch (\Exception $e) {
            Log::error('❌ Error respondiendo a contacto', [
                'user_email' => $contactData['email'],
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al enviar la respuesta.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ];
        }
    }

    /**
     * Enviar notificación al administrador
     */
    private function sendNotificationToAdmin(array $data): bool
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@tudominio.com');

        $subject = "Nuevo formulario de contacto: {$data['subject']}";
        $content = $this->buildAdminNotificationTemplate($data);

        return $this->sendEmailWithFallback($adminEmail, $subject, $content);
    }

    /**
     * Enviar confirmación al usuario
     */
    private function sendConfirmationToUser(array $data): bool
    {
        $subject = "Confirmación de recepción: {$data['subject']}";
        $content = $this->buildUserConfirmationTemplate($data);

        return $this->sendEmailWithFallback($data['email'], $subject, $content);
    }

    /**
     * Enviar email con sistema de fallback (Gmail → Brevo)
     */
    private function sendEmailWithFallback(string $toEmail, string $subject, string $content): bool
    {
        $emailSent = false;

        // Opción 1: Intentar con Gmail primero
        if ($this->useGmailAsPrimary && $this->isGmailConfigured()) {
            $emailSent = $this->sendEmailViaGmail($toEmail, $subject, $content);

            if ($emailSent) {
                Log::info('✅ Email enviado via Gmail', ['to' => $toEmail]);
                return true;
            } else {
                Log::warning('🔄 Fallback a Brevo: Gmail falló', ['to' => $toEmail]);
            }
        }

        // Opción 2: Usar Brevo API
        if (!$emailSent) {
            $emailSent = $this->sendEmailViaBrevo($toEmail, $subject, $content);

            if ($emailSent) {
                Log::info('✅ Email enviado via Brevo', ['to' => $toEmail]);
            } else {
                Log::error('❌ Ambos métodos de email fallaron', ['to' => $toEmail]);
            }
        }

        return $emailSent;
    }

    /**
     * Enviar respuesta al usuario con sistema de fallback
     */
    private function sendResponseEmailWithFallback(array $contactData, string $response): bool
    {
        $subject = "Respuesta a tu consulta: {$contactData['subject']}";
        $content = $this->buildResponseTemplate($contactData, $response);

        return $this->sendEmailWithFallback($contactData['email'], $subject, $content);
    }

    /**
     * Enviar email via Gmail SMTP
     */
    private function sendEmailViaGmail(string $toEmail, string $subject, string $content): bool
    {
        try {
            // Usar Mail::html() para enviar contenido HTML correctamente
            Mail::html($content, function ($message) use ($toEmail, $subject) {
                $message->to($toEmail)
                    ->subject($subject)
                    ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            });

            return true;
        } catch (\Exception $e) {
            Log::error('❌ Error enviando email via Gmail', [
                'to' => $toEmail,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Enviar email usando Brevo API
     */
    private function sendEmailViaBrevo(string $toEmail, string $subject, string $content): bool
    {
        try {
            $emailData = [
                'sender' => [
                    'name' => $this->brevoSenderName, // "Inca Dev"
                    'email' => $this->brevoSenderEmail, // "unsestudiante70@gmail.com"
                ],
                'to' => [
                    [
                        'email' => $toEmail,
                    ]
                ],
                'subject' => $subject,
                'htmlContent' => $content,
                'replyTo' => [
                    'email' => $this->brevoSenderEmail, // Mismo email para respuestas
                    'name' => $this->brevoSenderName,
                ]
            ];

            Log::info('📤 Enviando email via Brevo', [
                'from' => "{$this->brevoSenderName} <{$this->brevoSenderEmail}>",
                'to' => $toEmail,
                'subject' => $subject
            ]);

            $response = Http::withHeaders([
                'api-key' => $this->brevoApiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post('https://api.brevo.com/v3/smtp/email', $emailData);

            if ($response->successful()) {
                $messageId = $response->json('messageId');
                Log::info('✅ Email enviado correctamente via Brevo', [
                    'to' => $toEmail,
                    'message_id' => $messageId,
                    'from' => "{$this->brevoSenderName} <{$this->brevoSenderEmail}>"
                ]);
                return true;
            } else {
                Log::error('❌ Error enviando email via Brevo', [
                    'to' => $toEmail,
                    'status' => $response->status(),
                    'error' => $response->body(),
                    'from' => "{$this->brevoSenderName} <{$this->brevoSenderEmail}>"
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('❌ Excepción enviando email via Brevo', [
                'to' => $toEmail,
                'error' => $e->getMessage(),
                'from' => "{$this->brevoSenderName} <{$this->brevoSenderEmail}>"
            ]);
            return false;
        }
    }

    /**
     * Verificar si el mailer SMTP está configurado (Gmail o Brevo SMTP)
     */
    private function isGmailConfigured(): bool
    {
        $mailUser = env('MAIL_USERNAME');
        $mailPass = env('MAIL_PASSWORD');
        $mailHost = env('MAIL_HOST');

        return !empty($mailUser) && !empty($mailPass) && !empty($mailHost) &&
            env('MAIL_MAILER') === 'smtp';
    }

    /**
     * Templates de email
     */
    private function buildAdminNotificationTemplate(array $data): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; border: 1px solid #ddd; }
                .field { margin-bottom: 10px; }
                .label { font-weight: bold; color: #555; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>📧 Nuevo Formulario de Contacto</h1>
                <p>Se ha recibido un nuevo mensaje</p>
            </div>

            <div class='content'>
                <div class='field'>
                    <span class='label'>Nombre:</span> {$data['full_name']}
                </div>
                <div class='field'>
                    <span class='label'>Email:</span> {$data['email']}
                </div>
                " . ($data['phone'] ? "<div class='field'><span class='label'>Teléfono:</span> {$data['phone']}</div>" : "") . "
                " . ($data['company'] ? "<div class='field'><span class='label'>Empresa:</span> {$data['company']}</div>" : "") . "
                <div class='field'>
                    <span class='label'>Asunto:</span> {$data['subject']}
                </div>
                <div class='field'>
                    <span class='label'>Mensaje:</span>
                    <div style='background: white; padding: 15px; border-left: 4px solid #007bff; margin-top: 5px;'>
                        " . nl2br(htmlspecialchars($data['message'])) . "
                    </div>
                </div>
                <div class='field'>
                    <span class='label'>Fecha:</span> " . now()->format('d/m/Y H:i') . "
                </div>
            </div>
        </body>
        </html>
        ";
    }

    private function buildUserConfirmationTemplate(array $data): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <body>
            <h2>¡Hola {$data['full_name']}!</h2>
            <p>Hemos recibido tu mensaje correctamente. Aquí está un resumen:</p>
            
            <div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 15px 0;'>
                <p><strong>Asunto:</strong> {$data['subject']}</p>
                <p><strong>Mensaje:</strong></p>
                <p>" . nl2br(htmlspecialchars($data['message'])) . "</p>
            </div>
            
            <p>Nos pondremos en contacto contigo pronto.</p>
            <p><em>Equipo de " . config('app.name', 'Nuestra Empresa') . "</em></p>
        </body>
        </html>
        ";
    }

    private function buildResponseTemplate(array $contactData, string $response): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; border: 1px solid #ddd; }
                .response { background: white; border-left: 4px solid #28a745; padding: 15px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>¡Hola {$contactData['full_name']}!</h1>
                <p>Hemos respondido a tu consulta</p>
            </div>

            <div class='content'>
                <p>En respuesta a tu consulta sobre <strong>\"{$contactData['subject']}\"</strong>:</p>

                <div class='response'>
                    " . nl2br(htmlspecialchars($response)) . "
                </div>

                <p>Si tienes más preguntas, no dudes en contactarnos nuevamente.</p>

                <div style='text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;'>
                    <p><strong>Fecha de respuesta:</strong> " . now()->format('d/m/Y H:i') . "</p>
                    <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

}
