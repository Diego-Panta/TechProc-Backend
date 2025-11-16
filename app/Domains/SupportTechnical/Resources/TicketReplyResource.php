<?php

namespace App\Domains\SupportTechnical\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketReplyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'user_id' => $this->user_id,
            'content' => $this->content,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Relaciones
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name ?? $this->user->fullname ?? 'Usuario',
                    'email' => $this->user->email,
                ];
            }),
            
            'attachments' => ReplyAttachmentResource::collection($this->whenLoaded('attachments')),
            'attachments_count' => $this->when(
                $this->relationLoaded('attachments'),
                fn () => $this->attachments->count()
            ),
        ];
    }
}
