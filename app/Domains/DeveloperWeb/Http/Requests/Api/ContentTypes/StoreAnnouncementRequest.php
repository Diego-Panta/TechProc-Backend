<?php

namespace App\Domains\DeveloperWeb\Http\Requests\Api\ContentTypes;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Campos REQUERIDOS para ANNOUNCEMENT
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:20',
            'status' => 'required|string|in:draft,published,archived',
            'start_date' => 'required|date_format:Y-m-d H:i:s',
            'end_date' => 'required|date_format:Y-m-d H:i:s|after:start_date',
            'item_type' => 'required|string|in:banner,popup,sidebar,notification',
            
            // Campos OPCIONALES para ANNOUNCEMENT
            'image_url' => 'nullable|url|max:500',
            'target_page' => 'nullable|string|max:255',
            'link_url' => 'nullable|url|max:500',
            'button_text' => 'nullable|string|max:50',
            'priority' => 'nullable|integer|min:1|max:10',
        ];
    }
}