<?php

namespace App\Domains\Lms\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
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
            'course_id' => $this->course_id,
            'course' => [
                'id' => $this->course->id ?? null,
                'title' => $this->course->title ?? null,
                'name' => $this->course->name ?? null,
            ],
            'code' => $this->code,
            'name' => $this->name,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Contar participantes si la relación está cargada
            'participants_count' => $this->whenLoaded('groupParticipants', function () {
                return $this->groupParticipants->count();
            }),

            // Contar clases si la relación está cargada
            'classes_count' => $this->whenLoaded('classes', function () {
                return $this->classes->count();
            }),
        ];
    }
}
