<?php

namespace App\Domains\Security\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $this->when($this->relationLoaded('user'), fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'blocked_by' => $this->blocked_by,
            'blocked_by_user' => $this->when($this->relationLoaded('blockedByUser') && $this->blockedByUser, fn () => [
                'id' => $this->blockedByUser->id,
                'name' => $this->blockedByUser->name,
            ]),
            'reason' => $this->reason,
            'block_type' => $this->block_type,
            'block_type_label' => $this->block_type_label,
            'ip_address' => $this->ip_address,
            'blocked_at' => $this->blocked_at->toIso8601String(),
            'blocked_at_human' => $this->blocked_at->diffForHumans(),
            'blocked_until' => $this->blocked_until?->toIso8601String(),
            'blocked_until_human' => $this->blocked_until?->diffForHumans(),
            'is_active' => $this->is_active,
            'is_currently_blocked' => $this->is_currently_blocked,
            'remaining_time' => $this->remaining_time,
            'unblocked_at' => $this->unblocked_at?->toIso8601String(),
            'unblocked_by' => $this->unblocked_by,
            'unblocked_by_user' => $this->when($this->relationLoaded('unblockedByUser') && $this->unblockedByUser, fn () => [
                'id' => $this->unblockedByUser->id,
                'name' => $this->unblockedByUser->name,
            ]),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
