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
            'ticket_id' => $this->ticket_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'category' => $this->category,
            'user' => $this->when($this->relationLoaded('user') && $this->user, [
                'id' => $this->user?->id,
                'name' => $this->user ? $this->user->first_name . ' ' . $this->user->last_name : null,
                'email' => $this->user?->email,
            ]),
            'assigned_technician' => $this->when($this->relationLoaded('assignedTechnician') && $this->assignedTechnician, [
                'id' => $this->assignedTechnician?->id,
                'name' => $this->assignedTechnician ? $this->assignedTechnician->first_name . ' ' . $this->assignedTechnician->last_name : null,
            ]),
            'creation_date' => $this->creation_date?->toIso8601String(),
            'assignment_date' => $this->assignment_date?->toIso8601String(),
        ];
    }
}
