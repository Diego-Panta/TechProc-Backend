<?php

namespace App\Domains\SupportTechnical\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTicketRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:baja,media,alta,critica',
            'category' => 'required|string|max:100',
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
            'user_id.required' => 'El ID del usuario es obligatorio',
            'user_id.exists' => 'El usuario especificado no existe',
            'title.required' => 'El título del ticket es obligatorio',
            'description.required' => 'La descripción del ticket es obligatoria',
            'priority.required' => 'La prioridad es obligatoria',
            'priority.in' => 'La prioridad debe ser: baja, media, alta o critica',
            'category.required' => 'La categoría es obligatoria',
        ];
    }
}
