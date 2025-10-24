<?php

namespace App\Domains\Lms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseContentRequest extends FormRequest
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
            'course_id' => 'sometimes|integer|exists:courses,id',
            'session' => 'nullable|integer|min:1',
            'type' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'order_number' => 'nullable|integer|min:0',
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
            'course_id.integer' => 'El ID del curso debe ser un número entero',
            'course_id.exists' => 'El curso especificado no existe',
            'session.integer' => 'El número de sesión debe ser un número entero',
            'session.min' => 'El número de sesión debe ser al menos 1',
            'type.string' => 'El tipo debe ser una cadena de texto',
            'type.max' => 'El tipo no puede exceder 50 caracteres',
            'title.string' => 'El título debe ser una cadena de texto',
            'title.max' => 'El título no puede exceder 255 caracteres',
            'content.string' => 'El contenido debe ser una cadena de texto',
            'order_number.integer' => 'El número de orden debe ser un número entero',
            'order_number.min' => 'El número de orden debe ser al menos 0',
        ];
    }
}
