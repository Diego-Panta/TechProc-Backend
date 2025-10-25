<?php

namespace App\Domains\Lms\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassResource extends JsonResource
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
            'group_id' => $this->group_id,
            'group' => [
                'id' => $this->group->id ?? null,
                'code' => $this->group->code ?? null,
                'name' => $this->group->name ?? null,
                'course' => [
                    'id' => $this->group->course->id ?? null,
                    'title' => $this->group->course->title ?? null,
                ],
            ],
            'class_name' => $this->class_name,
            'meeting_url' => $this->meeting_url,
            'description' => $this->description,
            'class_date' => $this->class_date?->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'class_status' => $this->class_status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Contar asistencias si la relación está cargada
            'attendances_count' => $this->whenLoaded('attendances', function () {
                return $this->attendances->count();
            }),
        ];
    }
}
