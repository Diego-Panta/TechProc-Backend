<?php

namespace App\Domains\Lms\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
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
            'student_id' => $this->student_id,
            'user_id' => $this->user_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'company' => $this->when($this->relationLoaded('company') && $this->company, [
                'id' => $this->company?->id,
                'name' => $this->company?->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
