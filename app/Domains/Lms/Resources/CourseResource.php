<?php

namespace App\Domains\Lms\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'level' => $this->level,
            'course_image' => $this->course_image,
            'duration' => (float) $this->duration,
            'sessions' => $this->sessions,
            'selling_price' => (float) $this->selling_price,
            'discount_price' => $this->discount_price ? (float) $this->discount_price : null,
            'status' => (bool) $this->status,
            'bestseller' => (bool) $this->bestseller,
            'featured' => (bool) $this->featured,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
