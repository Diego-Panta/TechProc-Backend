<?php

namespace App\Domains\SupportTechnical\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReplyAttachmentResource extends JsonResource
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
            'type' => $this->type?->value,
            'path' => $this->path,
            'url' => $this->getFileUrl(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    /**
     * Get the file URL
     */
    private function getFileUrl(): string
    {
        // Si el path ya es una URL completa, retornarla
        if (filter_var($this->path, FILTER_VALIDATE_URL)) {
            return $this->path;
        }

        // Generar URL del storage
        return url('storage/' . $this->path);
    }
}
