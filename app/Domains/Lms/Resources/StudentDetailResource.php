<?php

namespace App\Domains\Lms\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentDetailResource extends JsonResource
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
            'document_number' => $this->document_number,
            'status' => $this->status,
            'company' => $this->when($this->relationLoaded('company') && $this->company, [
                'id' => $this->company?->id,
                'name' => $this->company?->name,
                'industry' => $this->company?->industry ?? null,
            ]),
            'enrollments' => $this->whenLoaded('enrollments', function () {
                return $this->enrollments->map(function ($enrollment) {
                    // Obtener el primer curso de enrollment_details
                    $firstDetail = $enrollment->enrollmentDetails->first();
                    $courseTitle = $firstDetail?->courseOffering?->course?->title ?? 'Sin título';
                    
                    return [
                        'enrollment_id' => $enrollment->enrollment_id,
                        'course_title' => $courseTitle,
                        'enrollment_date' => $enrollment->enrollment_date,
                        'status' => $enrollment->status,
                    ];
                });
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
