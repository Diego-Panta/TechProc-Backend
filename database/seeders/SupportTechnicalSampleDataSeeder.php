<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use IncadevUns\CoreDomain\Models\Ticket;
use IncadevUns\CoreDomain\Models\TicketReply;
use IncadevUns\CoreDomain\Enums\TicketStatus;
use IncadevUns\CoreDomain\Enums\TicketPriority;
use IncadevUns\CoreDomain\Enums\TicketType;
use Illuminate\Support\Facades\DB;

class SupportTechnicalSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Generando datos de muestra para SupportTechnical...');

        // Get users with different roles
        // Regular users: all users except admin, super_admin, and support
        $regularUsers = User::whereDoesntHave('roles', function ($query) {
            $query->whereIn('name', ['admin', 'super_admin', 'support']);
        })->limit(5)->get();
        $supportUsers = User::role(['support', 'admin'])->limit(2)->get();

        if ($regularUsers->isEmpty()) {
            $this->command->error('✗ No se encontraron usuarios regulares (sin roles admin, super_admin o support)');
            $this->command->warn('Por favor, ejecuta primero el seeder de usuarios:');
            $this->command->info('  php artisan db:seed --class="IncadevUns\CoreDomain\Database\Seeders\IncadevSeeder"');
            return;
        }

        if ($supportUsers->isEmpty()) {
            $this->command->warn('⚠ No se encontraron usuarios con rol "support" o "admin"');
            $this->command->info('Los tickets se crearán sin respuestas de soporte.');
        }

        DB::transaction(function () use ($regularUsers, $supportUsers) {
            $ticketsCreated = 0;
            $repliesCreated = 0;

            // Ticket samples data
            $ticketSamples = [
                // OPEN tickets
                [
                    'title' => 'No puedo acceder al sistema LMS',
                    'description' => 'Desde esta mañana no puedo ingresar al sistema LMS. Me aparece un error de "Credenciales inválidas" aunque estoy usando mi contraseña correcta.',
                    'type' => TicketType::Technical,
                    'priority' => TicketPriority::High,
                    'status' => TicketStatus::Open,
                    'replies_count' => 0,
                ],
                [
                    'title' => 'Solicitud de certificado académico',
                    'description' => 'Necesito un certificado de estudios para presentar en mi nuevo trabajo. ¿Cómo puedo solicitarlo?',
                    'type' => TicketType::Academic,
                    'priority' => TicketPriority::Medium,
                    'status' => TicketStatus::Open,
                    'replies_count' => 1,
                ],
                [
                    'title' => '¿Cómo exportar reportes a Excel?',
                    'description' => 'Necesito saber cómo puedo exportar los reportes del módulo de análisis de datos a formato Excel. No encuentro la opción.',
                    'type' => TicketType::Inquiry,
                    'priority' => TicketPriority::Low,
                    'status' => TicketStatus::Open,
                    'replies_count' => 2,
                ],

                // PENDING tickets
                [
                    'title' => 'Error al subir archivos grandes',
                    'description' => 'Cuando intento subir archivos mayores a 10MB, el sistema se queda cargando y eventualmente da timeout.',
                    'type' => TicketType::Technical,
                    'priority' => TicketPriority::High,
                    'status' => TicketStatus::Pending,
                    'replies_count' => 3,
                ],
                [
                    'title' => 'Actualización de datos personales',
                    'description' => 'Necesito actualizar mi dirección y número de teléfono en el sistema administrativo.',
                    'type' => TicketType::Administrative,
                    'priority' => TicketPriority::Low,
                    'status' => TicketStatus::Pending,
                    'replies_count' => 2,
                ],

                // CLOSED tickets
                [
                    'title' => 'No recibo notificaciones por correo',
                    'description' => 'Configuré las notificaciones pero no me llegan los correos. Ya revisé mi bandeja de spam.',
                    'type' => TicketType::Technical,
                    'priority' => TicketPriority::Medium,
                    'status' => TicketStatus::Closed,
                    'replies_count' => 4,
                ],
                [
                    'title' => 'Solicitud de constancia de matrícula',
                    'description' => 'Por favor, necesito una constancia de matrícula vigente para el trámite de beca.',
                    'type' => TicketType::Academic,
                    'priority' => TicketPriority::Medium,
                    'status' => TicketStatus::Closed,
                    'replies_count' => 2,
                ],
                [
                    'title' => '¿Cómo cambiar mi contraseña?',
                    'description' => 'Necesito instrucciones para cambiar mi contraseña de acceso al sistema.',
                    'type' => TicketType::Inquiry,
                    'priority' => TicketPriority::Low,
                    'status' => TicketStatus::Closed,
                    'replies_count' => 1,
                ],

                // More OPEN tickets for variety
                [
                    'title' => 'Dashboard no carga las estadísticas',
                    'description' => 'El dashboard principal se queda en blanco cuando intento ver las estadísticas del mes.',
                    'type' => TicketType::Technical,
                    'priority' => TicketPriority::High,
                    'status' => TicketStatus::Open,
                    'replies_count' => 0,
                ],
                [
                    'title' => 'Consulta sobre horarios de atención',
                    'description' => '¿Cuáles son los horarios de atención de la oficina de registro académico?',
                    'type' => TicketType::Inquiry,
                    'priority' => TicketPriority::Low,
                    'status' => TicketStatus::Open,
                    'replies_count' => 1,
                ],
            ];

            // Create tickets
            foreach ($ticketSamples as $index => $ticketData) {
                $user = $regularUsers[$index % $regularUsers->count()];
                $repliesCount = $ticketData['replies_count'];
                unset($ticketData['replies_count']);

                // Create ticket
                $ticket = Ticket::create([
                    'user_id' => $user->id,
                    'title' => $ticketData['title'],
                    'type' => $ticketData['type'],
                    'priority' => $ticketData['priority'],
                    'status' => $ticketData['status'],
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(0, 15)),
                ]);

                $ticketsCreated++;

                // Create initial reply (ticket description)
                $initialReply = TicketReply::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'content' => $ticketData['description'],
                    'created_at' => $ticket->created_at,
                    'updated_at' => $ticket->created_at,
                ]);

                $repliesCreated++;

                // Create additional replies if specified
                if ($repliesCount > 0 && $supportUsers->isNotEmpty()) {
                    for ($i = 0; $i < $repliesCount; $i++) {
                        $isFromSupport = $i % 2 === 0; // Alternate between support and user
                        $replyUser = $isFromSupport 
                            ? $supportUsers[$i % $supportUsers->count()] 
                            : $user;

                        $replyContent = $this->generateReplyContent($ticketData['type'], $isFromSupport, $i);

                        TicketReply::create([
                            'ticket_id' => $ticket->id,
                            'user_id' => $replyUser->id,
                            'content' => $replyContent,
                            'created_at' => $ticket->created_at->addHours(($i + 1) * 3),
                            'updated_at' => $ticket->created_at->addHours(($i + 1) * 3),
                        ]);

                        $repliesCreated++;
                    }
                }
            }

            $this->command->info("✓ {$ticketsCreated} tickets creados");
            $this->command->info("✓ {$repliesCreated} respuestas creadas");
        });

        $this->command->info('✓ Datos de muestra generados exitosamente');
    }

    /**
     * Generate appropriate reply content based on ticket type
     */
    private function generateReplyContent(TicketType $type, bool $isFromSupport, int $replyIndex): string
    {
        if ($isFromSupport) {
            $supportReplies = [
                TicketType::Technical->value => [
                    'Gracias por reportar el problema técnico. Nuestro equipo está investigando el caso.',
                    'Hemos identificado la causa del problema. Estamos trabajando en la solución.',
                    'El problema ha sido resuelto. Por favor, confirma si ahora funciona correctamente.',
                ],
                TicketType::Academic->value => [
                    'Recibimos tu solicitud académica. Estamos procesándola.',
                    'Tu solicitud ha sido aprobada y está en proceso.',
                    'La solicitud ha sido completada. Por favor, verifica.',
                ],
                TicketType::Administrative->value => [
                    'Tu solicitud administrativa está siendo revisada por el área correspondiente.',
                    'Hemos procesado tu solicitud. Te enviaremos la documentación por correo.',
                ],
                TicketType::Inquiry->value => [
                    'Gracias por tu consulta. Te proporciono la siguiente información:',
                    'Para realizar eso, debes seguir estos pasos: 1) Ir al menú principal, 2) Seleccionar la opción correspondiente.',
                ],
            ];

            $replies = $supportReplies[$type->value] ?? ['Gracias por contactarnos.'];
            return $replies[$replyIndex % count($replies)];
        } else {
            $userReplies = [
                'Gracias por la respuesta. Entiendo.',
                'Perfecto, ya probé y funciona correctamente.',
                '¿Podrían darme más detalles sobre esto?',
                'Muchas gracias por la ayuda.',
                'El problema persiste, aún no funciona.',
            ];

            return $userReplies[$replyIndex % count($userReplies)];
        }
    }
}
