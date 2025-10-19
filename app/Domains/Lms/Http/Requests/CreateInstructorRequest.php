<?php

namespace App\Domains\Lms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInstructorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // TODO: Implementar lógica de autorización cuando el módulo de autenticación esté disponible
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'bio' => 'nullable|string',
            'expertise_area' => 'required|string|max:500',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'El ID de usuario es obligatorio',
            'user_id.exists' => 'El usuario especificado no existe',
            'expertise_area.required' => 'El área de expertise es obligatoria',
            'status.required' => 'El estado es obligatorio',
            'status.in' => 'El estado debe ser: active o inactive',
        ];
    }
}
