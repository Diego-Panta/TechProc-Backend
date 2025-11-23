<?php

namespace App\Domains\Security\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecuritySettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'value' => $this->typed_value,
            'type' => $this->type,
            'description' => $this->description,
            'group' => $this->group,
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
