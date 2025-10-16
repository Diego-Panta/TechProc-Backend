<?php

namespace App\Domains\SupportTechnical\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EscalationResource extends JsonResource
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
            'escalation_id' => $this->escalation_id,
            'ticket' => $this->when($this->relationLoaded('ticket'), [
                'id' => $this->ticket?->id,
                'title' => $this->ticket?->title,
            ]),
            'technician_origin' => $this->when($this->relationLoaded('technicianOrigin'), [
                'id' => $this->technicianOrigin?->id,
                'name' => $this->technicianOrigin ? $this->technicianOrigin->first_name . ' ' . $this->technicianOrigin->last_name : null,
                'speciality' => $this->technicianOrigin?->position?->name ?? null,
            ]),
            'technician_destiny' => $this->when($this->relationLoaded('technicianDestiny'), [
                'id' => $this->technicianDestiny?->id,
                'name' => $this->technicianDestiny ? $this->technicianDestiny->first_name . ' ' . $this->technicianDestiny->last_name : null,
                'speciality' => $this->technicianDestiny?->position?->name ?? null,
            ]),
            'escalation_reason' => $this->escalation_reason,
            'observations' => $this->observations,
            'escalation_date' => $this->escalation_date?->toIso8601String(),
            'approved' => (bool) $this->approved,
        ];
    }
}
