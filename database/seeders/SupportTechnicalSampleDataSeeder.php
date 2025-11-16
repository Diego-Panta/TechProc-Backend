<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\AuthenticationSessions\Models\User;
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

        // Get users with student role
        $regularUsers = User::whereHas('roles', function($query) {
            $query->where('name', 'student');
        })->get();

        // If no users with student role, get any active users
        if ($regularUsers->isEmpty()) {
            $this->command->warn('No se encontraron usuarios con rol "student", usando cualquier usuario activo...');
            $regularUsers = User::where('email', '!=', 'admin@incadev.com')->limit(10)->get();
            
            if ($regularUsers->isEmpty()) {
                $this->command->error('No se encontraron usuarios disponibles');
                $this->command->warn('Por favor, ejecuta primero los seeders necesarios:');
                $this->command->info('  php artisan db:seed --class="IncadevUns\\CoreDomain\\Database\\Seeders\\UserSeeder"');
                return;
            }
        }

        // Get support users (support or admin roles)
        $supportUsers = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['support', 'admin']);
        })->get();

        if ($supportUsers->isEmpty()) {
            $this->command->warn('No se encontraron usuarios con rol "support" o "admin"');
            $this->command->info('Los tickets se crearán sin respuestas de soporte.');
        }

        $this->command->info("Encontrados {$regularUsers->count()} usuarios regulares");
        $this->command->info("Encontrados {$supportUsers->count()} usuarios de soporte");

        DB::transaction(function () use ($regularUsers, $supportUsers) {
            $ticketsCreated = 0;
            $repliesCreated = 0;

            // Ticket samples data
            $ticketSamples = [
                // OPEN tickets - Technical issues
                [
                    'title' => 'No puedo acceder al sistema LMS',
                    'description' => 'Desde esta mañana no puedo ingresar al sistema LMS. Me aparece un error de "Credenciales inválidas" aunque estoy usando mi contraseña correcta.',
                    'type' => TicketType::Technical,
                    'priority' => TicketPriority::High,
                    'status' => TicketStatus::Open,
                    'replies_count' => 0,
                ],
                [
                    'title' => 'Error al subir tarea en formato PDF',
                    'description' => 'Cuando intento subir mi tarea en PDF, el sistema muestra "Formato no válido". He intentado con diferentes archivos PDF.',
                    'type' => TicketType::Technical,
                    'priority' => TicketPriority::Medium,
                    'status' => TicketStatus::Open,
                    'replies_count' => 1,
                ],
                [
                    'title' => 'Video de clase no se reproduce',
                    'description' => 'El video de la clase del módulo 2 no se reproduce, se queda cargando indefinidamente.',
                    'type' => TicketType::Technical,
                    'priority' => TicketPriority::Medium,
                    'status' => TicketStatus::Open,
                    'replies_count' => 0,
                ],

                // OPEN tickets - Academic
                [
                    'title' => 'Solicitud de certificado académico',
                    'description' => 'Necesito un certificado de estudios para presentar en mi nuevo trabajo. ¿Cómo puedo solicitarlo?',
                    'type' => TicketType::Academic,
                    'priority' => TicketPriority::Medium,
                    'status' => TicketStatus::Open,
                    'replies_count' => 1,
                ],
                [
                    'title' => 'Consulta sobre fecha de examen final',
                    'description' => '¿Podrían confirmarme la fecha exacta del examen final del curso de IA? No la encuentro en el calendario.',
                    'type' => TicketType::Academic,
                    'priority' => TicketPriority::Low,
                    'status' => TicketStatus::Open,
                    'replies_count' => 1,
                ],

                // PENDING tickets
                [
                    'title' => 'Error al subir archivos grandes',
                    'description' => 'Cuando intento subir archivos mayores a 10MB, el sistema se queda cargando y eventualmente da timeout.',
                    'type' => TicketType::Technical,
                    'priority' => TicketPriority::High,
                    'status' => TicketStatus::Pending,
                    'replies_count' => 2,
                ],
                [
                    'title' => 'Actualización de datos personales',
                    'description' => 'Necesito actualizar mi dirección y número de teléfono en el sistema administrativo.',
                    'type' => TicketType::Administrative,
                    'priority' => TicketPriority::Low,
                    'status' => TicketStatus::Pending,
                    'replies_count' => 1,
                ],

                // CLOSED tickets - Resolved issues
                [
                    'title' => 'No recibo notificaciones por correo',
                    'description' => 'Configuré las notificaciones pero no me llegan los correos. Ya revisé mi bandeja de spam.',
                    'type' => TicketType::Technical,
                    'priority' => TicketPriority::Medium,
                    'status' => TicketStatus::Closed,
                    'replies_count' => 3,
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

                // More tickets for variety
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
                [
                    'title' => 'Problema con la plataforma de pago',
                    'description' => 'Al intentar realizar el pago de la matrícula, la plataforma me muestra un error de conexión.',
                    'type' => TicketType::Technical,
                    'priority' => TicketPriority::High,
                    'status' => TicketStatus::Pending,
                    'replies_count' => 2,
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
                TicketReply::create([
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

                    // Update ticket status based on replies
                    if ($ticketData['status'] === TicketStatus::Closed) {
                        $ticket->update([
                            'updated_at' => $ticket->created_at->addHours(($repliesCount + 1) * 3)
                        ]);
                    }
                }
            }

            $this->command->info("{$ticketsCreated} tickets creados");
            $this->command->info("{$repliesCreated} respuestas creadas");
        });

        $this->command->info('Datos de muestra generados exitosamente');
    }

    /**
     * Generate appropriate reply content based on ticket type
     */
    private function generateReplyContent(TicketType $type, bool $isFromSupport, int $replyIndex): string
    {
        if ($isFromSupport) {
            $supportReplies = [
                TicketType::Technical->value => [
                    'Gracias por reportar el problema. Nuestro equipo técnico está investigando el caso.',
                    'Hemos identificado la causa del problema y estamos trabajando en la solución.',
                    'El problema ha sido resuelto. Por favor, verifica si ahora funciona correctamente.',
                    'Hemos aplicado una solución temporal mientras trabajamos en la corrección permanente.',
                ],
                TicketType::Academic->value => [
                    'Recibimos tu solicitud académica. Está siendo procesada por el área correspondiente.',
                    'Tu solicitud ha sido aprobada. Te enviaremos la documentación en los próximos días.',
                    'La solicitud ha sido completada. Puedes descargar el documento desde tu panel.',
                ],
                TicketType::Administrative->value => [
                    'Tu solicitud administrativa está siendo revisada.',
                    'Hemos procesado tu solicitud. Recibirás un correo de confirmación.',
                    'Necesitamos que completes el formulario adjunto para proceder con tu solicitud.',
                ],
                TicketType::Inquiry->value => [
                    'Gracias por tu consulta. Te proporcionamos la siguiente información:',
                    'Para realizar esa acción, sigue estos pasos: 1) Ve al menú principal, 2) Selecciona la opción correspondiente, 3) Completa los datos requeridos.',
                    'La información que solicitas está disponible en la sección "Ayuda" del sistema.',
                ],
            ];

            $replies = $supportReplies[$type->value] ?? ['Gracias por contactarnos. Estamos procesando tu solicitud.'];
            return $replies[$replyIndex % count($replies)];
        } else {
            $userReplies = [
                'Gracias por la respuesta. Voy a probar la solución.',
                'Perfecto, ya funciona correctamente. Muchas gracias.',
                '¿Podrían darme más información sobre el plazo de solución?',
                'El problema persiste, aún no puedo acceder.',
                'Muchas gracias por la ayuda, todo solucionado.',
                '¿Hay algún número de referencia para mi caso?',
            ];

            return $userReplies[$replyIndex % count($userReplies)];
        }
    }
}