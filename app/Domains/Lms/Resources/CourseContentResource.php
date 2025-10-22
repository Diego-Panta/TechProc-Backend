<?php

namespace App\Domains\Lms\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseContentResource extends JsonResource
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
            'course' => $this->when($this->relationLoaded('course'), [
                'id' => $this->course?->id,
                'course_id' => $this->course?->course_id,
                'title' => $this->course?->title,
            ]),
            'session' => $this->session,
            'type' => $this->type,
            'title' => $this->title,
            'content' => $this->content,
            'order_number' => $this->order_number,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
