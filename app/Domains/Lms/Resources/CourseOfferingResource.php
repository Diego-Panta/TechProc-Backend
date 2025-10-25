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
                    'course_id' => $this->course->course_id,
                    'title' => $this->course->title,
                    'name' => $this->course->name,
                    'description' => $this->course->description,
                    'level' => $this->course->level,
                    'course_image' => $this->course->course_image,
                    'video_url' => $this->course->video_url,
                    'duration' => $this->course->duration,
                    'sessions' => $this->course->sessions,
                    'selling_price' => $this->course->selling_price,
                    'discount_price' => $this->course->discount_price,
                    'prerequisites' => $this->course->prerequisites,
                    'certificate_name' => $this->course->certificate_name,
                    'certificate_issuer' => $this->course->certificate_issuer,
                    'bestseller' => $this->course->bestseller,
                    'featured' => $this->course->featured,
                    'highest_rated' => $this->course->highest_rated,
                    'status' => $this->course->status,
                    'created_at' => $this->course->created_at?->toIso8601String(),
                ];
            }),
            'academic_period_id' => $this->academic_period_id,
            'academic_period' => $this->when($this->relationLoaded('academicPeriod'), function () {
                return [
                    'id' => $this->academicPeriod->id,
                    'academic_period_id' => $this->academicPeriod->academic_period_id,
                    'name' => $this->academicPeriod->name,
                    'start_date' => $this->academicPeriod->start_date?->toIso8601String(),
                    'end_date' => $this->academicPeriod->end_date?->toIso8601String(),
                    'status' => $this->academicPeriod->status,
                    'created_at' => $this->academicPeriod->created_at?->toIso8601String(),
                ];
            }),
            'instructor_id' => $this->instructor_id,
            'instructor' => $this->when($this->relationLoaded('instructor') && $this->instructor, function () {
                return [
                    'id' => $this->instructor->id,
                    'instructor_id' => $this->instructor->instructor_id,
                    'user_id' => $this->instructor->user_id,
                    'bio' => $this->instructor->bio,
                    'expertise_area' => $this->instructor->expertise_area,
                    'status' => $this->instructor->status,
                    'user' => $this->when($this->instructor->relationLoaded('user'), function () {
                        return [
                            'id' => $this->instructor->user->id,
                            'first_name' => $this->instructor->user->first_name,
                            'last_name' => $this->instructor->user->last_name,
                            'full_name' => $this->instructor->user->full_name,
                            'email' => $this->instructor->user->email,
                            'phone_number' => $this->instructor->user->phone_number,
                            'profile_photo' => $this->instructor->user->profile_photo,
                        ];
                    }),
                ];
            }),
            'schedule' => $this->schedule,
            'delivery_method' => $this->delivery_method,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
