<?php

namespace App\Domains\Lms\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorResource extends JsonResource
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
            'instructor_id' => $this->instructor_id,
            'user_id' => $this->user_id,
            'name' => $this->when($this->relationLoaded('user') && $this->user, 
                $this->user?->first_name . ' ' . $this->user?->last_name
            ),
            'email' => $this->when($this->relationLoaded('user') && $this->user, 
                $this->user?->email
            ),
            'bio' => $this->bio,
            'expertise_area' => $this->expertise_area,
            'status' => $this->status,
            'courses_count' => $this->when(isset($this->courses_count), $this->courses_count),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
