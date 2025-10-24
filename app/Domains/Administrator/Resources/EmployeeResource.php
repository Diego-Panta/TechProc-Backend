<?php

namespace App\Domains\Administrator\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'hire_date' => $this->hire_date ? $this->hire_date->format('Y-m-d') : null,
            'employment_status' => $this->employment_status,
            'schedule' => $this->schedule,
            'speciality' => $this->speciality,
            'salary' => $this->salary,
            'user' => new UserResource($this->whenLoaded('user')),
            'position' => [
                'id' => $this->whenLoaded('position', function () {
                    return $this->position->id;
                }),
                'position_name' => $this->whenLoaded('position', function () {
                    return $this->position->position_name;
                }),
            ],
            'department' => [
                'id' => $this->whenLoaded('department', function () {
                    return $this->department->id;
                }),
                'department_name' => $this->whenLoaded('department', function () {
                    return $this->department->department_name;
                }),
                'description' => $this->whenLoaded('department', function () {
                    return $this->department->description;
                }),
            ],
            //'created_at' => $this->created_at->toISOString(),
            //'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}
