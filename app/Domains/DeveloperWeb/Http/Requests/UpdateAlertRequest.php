<?php

namespace App\Domains\DeveloperWeb\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Temporalmente siempre true
    }

    public function rules(): array
    {
        return [
            'message' => 'sometimes|string|min:5|max:1000',
            'type' => 'sometimes|string|in:info,warning,error,success,maintenance',
            'status' => 'sometimes|string|in:active,inactive',
            'link_url' => 'nullable|url|max:500',
            'link_text' => 'nullable|string|max:100',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'priority' => 'sometimes|integer|min:1|max:5',
        ];
    }

    public function messages(): array
    {
        return [
            'message.min' => 'El mensaje debe tener al menos 5 caracteres',
            'message.max' => 'El mensaje no puede exceder los 1000 caracteres',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            'priority.min' => 'La prioridad debe ser al menos 1',
            'priority.max' => 'La prioridad no puede ser mayor a 5',
        ];
    }
}