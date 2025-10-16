<?php

namespace App\Domains\Lms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'level' => 'sometimes|in:basic,intermediate,advanced',
            'course_image' => 'nullable|string|max:500',
            'video_url' => 'nullable|string|max:500',
            'duration' => 'sometimes|numeric|min:0',
            'sessions' => 'sometimes|integer|min:1',
            'selling_price' => 'sometimes|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'prerequisites' => 'nullable|string',
            'certificate_name' => 'nullable|boolean',
            'certificate_issuer' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
            'instructor_ids' => 'nullable|array',
            'instructor_ids.*' => 'integer|exists:instructors,id',
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
            'title.string' => 'El título debe ser una cadena de texto',
            'level.in' => 'El nivel debe ser: basic, intermediate o advanced',
            'duration.numeric' => 'La duración debe ser un número',
            'sessions.integer' => 'El número de sesiones debe ser un número entero',
            'selling_price.numeric' => 'El precio de venta debe ser un número',
            'category_ids.*.exists' => 'Una o más categorías no existen',
            'instructor_ids.*.exists' => 'Uno o más instructores no existen',
        ];
    }
}
