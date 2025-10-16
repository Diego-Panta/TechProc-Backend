<?php

namespace App\Domains\DeveloperWeb\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Temporalmente siempre true
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string|min:5|max:1000',
            'type' => 'required|string|in:info,warning,error,success,maintenance',
            'status' => 'required|string|in:active,inactive',
            'link_url' => 'nullable|url|max:500',
            'link_text' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'priority' => 'required|integer|min:1|max:5',
            'created_by' => 'nullable|integer|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'El mensaje de la alerta es obligatorio',
            'message.min' => 'El mensaje debe tener al menos 5 caracteres',
            'message.max' => 'El mensaje no puede exceder los 1000 caracteres',
            'type.required' => 'El tipo de alerta es obligatorio',
            'status.required' => 'El estado de la alerta es obligatorio',
            'start_date.required' => 'La fecha de inicio es obligatoria',
            'end_date.required' => 'La fecha de fin es obligatoria',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            'priority.required' => 'La prioridad es obligatoria',
            'priority.min' => 'La prioridad debe ser al menos 1',
            'priority.max' => 'La prioridad no puede ser mayor a 5',
        ];
    }
}