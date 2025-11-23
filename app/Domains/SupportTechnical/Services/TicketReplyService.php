<?php

namespace App\Domains\SupportTechnical\Services;

use IncadevUns\CoreDomain\Models\TicketReply;
use IncadevUns\CoreDomain\Models\ReplyAttachment;
use IncadevUns\CoreDomain\Enums\MediaType;
use App\Domains\SupportTechnical\Repositories\TicketRepositoryInterface;
use App\Domains\SupportTechnical\Notifications\TicketReplyReceivedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class TicketReplyService
{
    protected TicketRepositoryInterface $repository;

    public function __construct(TicketRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a reply with optional attachments
     */
    public function createReply(int $ticketId, array $data, int $userId, ?array $files = null, bool $isSupport = false): TicketReply
    {
        return DB::transaction(function () use ($ticketId, $data, $userId, $files, $isSupport) {
            // Verify ticket exists and is not closed
            $ticket = $this->repository->findById($ticketId);

            if (!$ticket) {
                throw new \Exception('Ticket no encontrado');
            }

            if ($ticket->status->value === 'closed') {
                throw new \Exception('No se puede responder a un ticket cerrado');
            }

            // Si es un usuario de soporte respondiendo y el ticket está en estado 'pending', cambiar a 'open'
            if ($isSupport && $ticket->status->value === 'pending') {
                $this->repository->update($ticketId, [
                    'status' => \IncadevUns\CoreDomain\Enums\TicketStatus::Open,
                ]);
                $ticket->refresh();
            }

            // Create the reply
            $reply = $this->repository->createReply($ticketId, [
                'user_id' => $userId,
                'content' => $data['content'],
            ]);

            // Handle attachments if provided
            if ($files && is_array($files)) {
                foreach ($files as $file) {
                    $this->storeAttachment($reply->id, $ticketId, $file);
                }
            }

            $replyWithRelations = $reply->load(['user', 'attachments']);

            // Enviar notificación al dueño del ticket si la respuesta NO es del dueño
            // (evitar notificar al usuario cuando responde su propio ticket)
            if ($ticket->user_id !== $userId) {
                $ticket->user->notify(new TicketReplyReceivedNotification(
                    $ticket,
                    $replyWithRelations
                ));
            }

            return $replyWithRelations;
        });
    }

    /**
     * Update a reply
     */
    public function updateReply(int $replyId, array $data, int $userId, bool $isSupport = false): TicketReply
    {
        $reply = $this->repository->findReplyById($replyId);
        
        if (!$reply) {
            throw new \Exception('Respuesta no encontrada');
        }

        // Si es el autor (no soporte), verificar límite de 24 horas
        if (!$isSupport && $reply->user_id === $userId) {
            $hoursSinceCreation = $reply->created_at->diffInHours(now());
            
            if ($hoursSinceCreation > 24) {
                throw new \Exception(
                    "No puedes editar esta respuesta porque han pasado más de 24 horas desde su creación. " .
                    "Tiempo transcurrido: " . round($hoursSinceCreation, 1) . " horas."
                );
            }
        }

        return $this->repository->updateReply($replyId, [
            'content' => $data['content'],
        ]);
    }

    /**
     * Delete a reply
     */
    public function deleteReply(int $replyId): bool
    {
        $reply = $this->repository->findReplyById($replyId);
        
        if (!$reply) {
            throw new \Exception('Respuesta no encontrada');
        }

        // Check if it's the first reply (cannot be deleted)
        $firstReply = $reply->ticket->replies()->orderBy('id')->first();
        if ($firstReply && $firstReply->id === $reply->id) {
            throw new \Exception('No puedes eliminar la primera respuesta del ticket');
        }

        return $this->repository->deleteReply($replyId);
    }

    /**
     * Store an attachment
     */
    private function storeAttachment(int $replyId, int $ticketId, UploadedFile $file): ReplyAttachment
    {
        // Generate path: tickets/{ticketId}/replies/{replyId}/filename
        $path = $file->store("tickets/{$ticketId}/replies/{$replyId}", 'public');

        // Determine media type based on file extension
        $extension = $file->getClientOriginalExtension();
        $mediaType = $this->determineMediaType($extension);

        return $this->repository->createAttachment($replyId, [
            'type' => $mediaType,
            'path' => $path,
        ]);
    }

    /**
     * Determine media type from extension
     */
    private function determineMediaType(string $extension): MediaType
    {
        return match(strtolower($extension)) {
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp' => MediaType::Image,
            'pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx' => MediaType::Document,
            'mp4', 'avi', 'mov', 'wmv' => MediaType::Video,
            'mp3', 'wav', 'ogg' => MediaType::Audio,
            'zip', 'rar', '7z' => MediaType::Other,
            default => MediaType::Other,
        };
    }

    /**
     * Delete an attachment
     */
    public function deleteAttachment(int $attachmentId): bool
    {
        $attachment = $this->repository->findAttachmentById($attachmentId);
        
        if (!$attachment) {
            throw new \Exception('Archivo no encontrado');
        }

        return $this->repository->deleteAttachment($attachmentId);
    }

    /**
     * Get attachment for download
     */
    public function getAttachmentForDownload(int $attachmentId, int $userId, bool $isSupport = false): ?ReplyAttachment
    {
        $attachment = $this->repository->findAttachmentById($attachmentId);
        
        if (!$attachment) {
            return null;
        }

        $ticket = $attachment->ticketReply->ticket;

        // Check if user has access to the ticket
        if (!$isSupport && $ticket->user_id !== $userId) {
            throw new \Exception('No tienes permiso para descargar este archivo');
        }

        return $attachment;
    }

    /**
     * Get reply by ID
     */
    public function getReplyById(int $replyId): ?TicketReply
    {
        return $this->repository->findReplyById($replyId);
    }

    /**
     * Get attachment by ID
     */
    public function getAttachmentById(int $attachmentId): ?ReplyAttachment
    {
        return $this->repository->findAttachmentById($attachmentId);
    }
}
