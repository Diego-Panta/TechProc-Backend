<?php

namespace App\Domains\DeveloperWeb\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateAlertApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'sometimes|string|min:5|max:1000',
            'type' => 'sometimes|string|in:info,warning,error,success,maintenance',
            'status' => 'sometimes|string|in:active,inactive',
            'link_url' => 'nullable|url|max:500',
            'link_text' => 'nullable|string|max:100',
            'start_date' => 'sometimes|date|after_or_equal:today',
            'end_date' => 'required_with:start_date|date|after:start_date',
            'priority' => 'sometimes|integer|min:1|max:5',
        ];
    }

    public function messages(): array
    {
        return [
            'message.min' => 'El mensaje debe tener al menos 5 caracteres',
            'message.max' => 'El mensaje no puede exceder los 1000 caracteres',
            'start_date.after_or_equal' => 'La fecha de inicio no puede ser en el pasado',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
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