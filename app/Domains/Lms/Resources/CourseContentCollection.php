<?php

namespace App\Domains\Lms\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CourseContentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => CourseContentResource::collection($this->collection),
            'current_page' => $this->currentPage(),
            'per_page' => $this->perPage(),
            'total' => $this->total(),
            'last_page' => $this->lastPage(),
        ];
    }
}
