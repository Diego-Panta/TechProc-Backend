<?php

namespace App\Domains\Security\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'abilities' => json_decode($this->abilities ?? '[]'),
            'last_used_at' => $this->last_used_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'is_expired' => $this->expires_at && $this->expires_at->isPast(),
            'days_until_expiry' => $this->expires_at ? now()->diffInDays($this->expires_at, false) : null,
        ];
    }
}
