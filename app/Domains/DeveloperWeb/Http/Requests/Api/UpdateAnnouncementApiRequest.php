<?php

namespace App\Domains\DeveloperWeb\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateAnnouncementApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Temporalmente sin autenticación
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string|min:10',
            'image_url' => 'nullable|url|max:500',
            'display_type' => 'sometimes|string|in:banner,modal,popup,notification',
            'target_page' => 'sometimes|string|max:100',
            'link_url' => 'nullable|url|max:500',
            'button_text' => 'nullable|string|max:100',
            'status' => 'sometimes|string|in:draft,published,archived',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
        ];
    }

    public function messages(): array
    {
        return [
            'content.min' => 'El contenido debe tener al menos 10 caracteres',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
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