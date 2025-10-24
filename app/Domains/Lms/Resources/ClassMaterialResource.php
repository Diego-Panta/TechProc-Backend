<?php

namespace App\Domains\Lms\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassMaterialResource extends JsonResource
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
            'class_id' => $this->class_id,
            'class' => [
                'id' => $this->class->id ?? null,
                'class_name' => $this->class->class_name ?? null,
                'class_date' => $this->class->class_date?->format('Y-m-d') ?? null,
                'group' => [
                    'id' => $this->class->group->id ?? null,
                    'name' => $this->class->group->name ?? null,
                    'course' => [
                        'id' => $this->class->group->course->id ?? null,
                        'title' => $this->class->group->course->title ?? null,
                    ],
                ],
            ],
            'material_url' => $this->material_url,
            'type' => $this->type,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
