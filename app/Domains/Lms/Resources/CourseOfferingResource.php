<?php

namespace App\Domains\Lms\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseOfferingResource extends JsonResource
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
            'course_offering_id' => $this->course_offering_id,
            'course_id' => $this->course_id,
            'course' => $this->when($this->relationLoaded('course'), function () {
                return [
                    'id' => $this->course->id,
                    'name' => $this->course->name ?? null,
                    'title' => $this->course->title ?? null,
                ];
            }),
            'academic_period_id' => $this->academic_period_id,
            'academic_period' => $this->when($this->relationLoaded('academicPeriod'), function () {
                return [
                    'id' => $this->academicPeriod->id,
                    'name' => $this->academicPeriod->name,
                    'start_date' => $this->academicPeriod->start_date?->toIso8601String(),
                    'end_date' => $this->academicPeriod->end_date?->toIso8601String(),
                ];
            }),
            'instructor_id' => $this->instructor_id,
            'instructor' => $this->when($this->relationLoaded('instructor') && $this->instructor, function () {
                return [
                    'id' => $this->instructor->id,
                    'first_name' => $this->instructor->user?->first_name ?? null,
                    'last_name' => $this->instructor->user?->last_name ?? null,
                    'email' => $this->instructor->user?->email ?? null,
                ];
            }),
            'schedule' => $this->schedule,
            'delivery_method' => $this->delivery_method,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
