<?php

namespace App\Domains\SupportTechnical\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
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
            'user_id' => $this->user_id,
            'title' => $this->title,
            'type' => $this->type?->value,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
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
            
            'replies' => TicketReplyResource::collection($this->whenLoaded('replies')),
            'replies_count' => $this->when(
                $this->relationLoaded('replies'),
                fn () => $this->replies->count()
            ),
            
            // Última respuesta (solo si está cargada)
            'last_reply' => $this->when(
                $this->relationLoaded('replies') && $this->replies->isNotEmpty(),
                function () {
                    $lastReply = $this->replies->last();
                    return $lastReply ? [
                        'id' => $lastReply->id,
                        'content' => $lastReply->content,
                        'created_at' => $lastReply->created_at?->toISOString(),
                        'user' => [
                            'id' => $lastReply->user->id,
                            'name' => $lastReply->user->name ?? $lastReply->user->fullname ?? 'Usuario',
                        ],
                    ] : null;
                }
            ),
        ];
    }
}
