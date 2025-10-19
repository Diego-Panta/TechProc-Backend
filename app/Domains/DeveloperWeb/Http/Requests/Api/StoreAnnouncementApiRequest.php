<?php

namespace App\Domains\DeveloperWeb\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreAnnouncementApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Temporalmente sin autenticación
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'image_url' => 'nullable|url|max:500',
            'display_type' => 'required|string|in:banner,modal,popup,notification',
            'target_page' => 'required|string|max:100',
            'link_url' => 'nullable|url|max:500',
            'button_text' => 'nullable|string|max:100',
            'status' => 'required|string|in:draft,published,archived',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio',
            'content.required' => 'El contenido es obligatorio',
            'content.min' => 'El contenido debe tener al menos 10 caracteres',
            'display_type.required' => 'El tipo de visualización es obligatorio',
            'target_page.required' => 'La página objetivo es obligatoria',
            'start_date.required' => 'La fecha de inicio es obligatoria',
            'start_date.after_or_equal' => 'La fecha de inicio no puede ser en el pasado',
            'end_date.required' => 'La fecha de fin es obligatoria',
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