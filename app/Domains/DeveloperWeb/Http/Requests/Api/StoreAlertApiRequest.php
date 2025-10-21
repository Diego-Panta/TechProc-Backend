<?php

namespace App\Domains\DeveloperWeb\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreAlertApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // El middleware se encarga de la autorización
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string|min:5|max:1000',
            'type' => 'required|string|in:info,warning,error,success,maintenance',
            'status' => 'required|string|in:active,inactive',
            'link_url' => 'nullable|url|max:500',
            'link_text' => 'nullable|string|max:100',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'priority' => 'required|integer|min:1|max:5',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'El mensaje de la alerta es obligatorio',
            'message.min' => 'El mensaje debe tener al menos 5 caracteres',
            'message.max' => 'El mensaje no puede exceder los 1000 caracteres',
            'type.required' => 'El tipo de alerta es obligatorio',
            'type.in' => 'El tipo de alerta no es válido',
            'status.required' => 'El estado de la alerta es obligatorio',
            'status.in' => 'El estado de la alerta no es válido',
            'link_url.url' => 'La URL del enlace no es válida',
            'start_date.required' => 'La fecha de inicio es obligatoria',
            'start_date.date' => 'La fecha de inicio debe ser una fecha válida',
            'start_date.after_or_equal' => 'La fecha de inicio debe ser hoy o una fecha futura',
            'end_date.required' => 'La fecha de fin es obligatoria',
            'end_date.date' => 'La fecha de fin debe ser una fecha válida',
            'end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio',
            'priority.required' => 'La prioridad es obligatoria',
            'priority.integer' => 'La prioridad debe ser un número entero',
            'priority.min' => 'La prioridad debe ser al menos 1',
            'priority.max' => 'La prioridad no puede ser mayor a 5',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422));
    }
}