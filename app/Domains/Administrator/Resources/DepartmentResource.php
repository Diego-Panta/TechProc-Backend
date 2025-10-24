<?php

namespace App\Domains\Administrator\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
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
            'department_name' => $this->department_name,
            'description' => $this->description,
            'positions_count' => $this->when(isset($this->positions_count), $this->positions_count),
            'employees_count' => $this->when(isset($this->employees_count), $this->employees_count),
            'positions' => PositionResource::collection($this->whenLoaded('positions')),
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
