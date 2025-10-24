<?php

namespace App\Domains\Lms\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'enrollment_id' => $this->enrollment_id,
            'student' => $this->when($this->relationLoaded('student'), [
                'id' => $this->student?->id,
                'name' => $this->student ? $this->student->first_name . ' ' . $this->student->last_name : null,
            ]),
            'academic_period' => $this->when($this->relationLoaded('academicPeriod'), [
                'id' => $this->academicPeriod?->id,
                'name' => $this->academicPeriod?->name,
            ]),
            'enrollment_date' => $this->enrollment_date?->format('Y-m-d'),
            'status' => $this->status,
            'courses' => $this->whenLoaded('enrollmentDetails', function () {
                return $this->enrollmentDetails->map(function ($detail) {
                    return [
                        'course_offering_id' => $detail->course_offering_id,
                        'course_title' => $detail->courseOffering?->course?->title ?? 'Sin título',
                    ];
                });
            }),
        ];
    }
}
