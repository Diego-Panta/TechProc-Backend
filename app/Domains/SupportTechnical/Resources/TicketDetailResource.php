<?php

namespace App\Domains\SupportTechnical\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketDetailResource extends JsonResource
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
            'notes' => $this->notes,
            'user' => $this->when($this->relationLoaded('user') && $this->user, [
                'id' => $this->user?->id,
                'name' => $this->user ? $this->user->first_name . ' ' . $this->user->last_name : null,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone_number ?? null,
            ]),
            'assigned_technician' => $this->when($this->relationLoaded('assignedTechnician') && $this->assignedTechnician, [
                'id' => $this->assignedTechnician?->id,
                'employee_id' => $this->assignedTechnician?->employee_id,
                'name' => $this->assignedTechnician ? $this->assignedTechnician->first_name . ' ' . $this->assignedTechnician->last_name : null,
                'speciality' => $this->assignedTechnician?->position?->name ?? null,
            ]),
            'creation_date' => $this->creation_date?->toIso8601String(),
            'assignment_date' => $this->assignment_date?->toIso8601String(),
            'resolution_date' => $this->resolution_date?->toIso8601String(),
            'close_date' => $this->close_date?->toIso8601String(),
            'tracking' => $this->whenLoaded('ticketTrackings', function () {
                return $this->ticketTrackings->map(function ($tracking) {
                    return [
                        'ticket_tracking_id' => $tracking->ticket_tracking_id,
                        'comment' => $tracking->comment,
                        'action_type' => $tracking->action_type,
                        'follow_up_date' => $tracking->follow_up_date?->toIso8601String(),
                    ];
                });
            }),
        ];
    }
}
