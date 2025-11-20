<?php

namespace App\Domains\Security\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'ip_address' => $this->ip_address,
            'device' => $this->device,
            'user_agent' => $this->user_agent,
            'last_activity' => $this->last_activity,
            'last_activity_human' => $this->last_activity_human,
            'is_active' => $this->is_active,
            'is_current' => $this->id === session()->getId(),
        ];
    }
}
