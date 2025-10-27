<?php

namespace Database\Seeders;

use App\Domains\DeveloperWeb\Models\ContactForm;
use Illuminate\Database\Seeder;

class ContactFormSeeder extends Seeder
{
    public function run(): void
    {
        $contactForms = [
            [
                'id_contact' => 1,
                'full_name' => 'María López',
                'email' => 'maria.lopez@ejemplo.com',
                'phone' => '+51 987654321',
                'company' => 'Tech Solutions SAC',
                'subject' => 'Consulta sobre cursos corporativos',
                'message' => 'Me gustaría información sobre los cursos de desarrollo web que ofrecen para nuestro equipo de trabajo. ¿Tienen programas corporativos?',
                'form_type' => 'sales',
                'status' => 'pending',
                'submission_date' => now()->subDays(2)
            ],
            [
                'id_contact' => 3,
                'full_name' => 'Laura Mendoza',
                'email' => 'laura.mendoza@gmail.com',
                'phone' => '+51 987654323',
                'company' => null,
                'subject' => 'Problema con el certificado',
                'message' => 'He completado el curso pero no puedo descargar mi certificado. El sistema me muestra un error.',
                'form_type' => 'support',
                'status' => 'responded',
                'assigned_to' => 2, // Asignado a María García
                'response' => 'Estimada Laura, hemos verificado tu caso y el certificado ya está disponible para descarga. Por favor, intenta nuevamente. Si el problema persiste, contáctanos.',
                'response_date' => now()->subHours(5),
                'submission_date' => now()->subDays(3)
            ],
            [
                'id_contact' => 4,
                'full_name' => 'Roberto Silva',
                'email' => 'roberto.silva@outlook.com',
                'phone' => '+51 987654324',
                'company' => 'Digital Solutions',
                'subject' => 'Alianza estratégica',
                'message' => 'Estamos interesados en establecer una alianza estratégica para ofrecer sus cursos a nuestros clientes.',
                'form_type' => 'partnership',
                'status' => 'pending',
                'submission_date' => now()->subHours(12)
            ],
            [
                'id_contact' => 5,
                'full_name' => 'Promoción Gratuita',
                'email' => 'spam@fake.com',
                'phone' => null,
                'company' => 'Spam Company',
                'subject' => 'Gana dinero fácil',
                'message' => '¡Promoción especial! Gana $5000 trabajando desde casa...',
                'form_type' => 'general',
                'status' => 'spam',
                'submission_date' => now()->subDays(1)
            ]
        ];

        foreach ($contactForms as $contactForm) {
            ContactForm::create($contactForm);
        }
    }
}