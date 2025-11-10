<?php

namespace App\Domains\DeveloperWeb\Http\Requests\Api\ContentTypes;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Campos REQUERIDOS para ALERT
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10|max:1000',
            'status' => 'required|string|in:draft,published,archived',
            'start_date' => 'required|date_format:Y-m-d H:i:s',
            'end_date' => 'required|date_format:Y-m-d H:i:s|after:start_date',
            'item_type' => 'required|string|in:info,warning,error,success',
            
            // Campos OPCIONALES para ALERT
            'link_url' => 'nullable|url|max:500',
            'link_text' => 'nullable|string|max:50',
            'priority' => 'nullable|integer|min:1|max:10',
        ];
    }
}