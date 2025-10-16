<?php

namespace App\Domains\SupportTechnical\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TicketCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tickets' => TicketResource::collection($this->collection),
            'pagination' => [
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
                'total_records' => $this->total(),
                'per_page' => $this->perPage(),
            ],
        ];
    }
}
