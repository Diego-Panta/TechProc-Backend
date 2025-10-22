<?php

namespace App\Domains\Lms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCourseRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'name' => 'nullable|string|max:200',
            'description' => 'required|string',
            'level' => 'required|in:basic,intermediate,advanced',
            'course_image' => 'nullable|string|max:255',
            'video_url' => 'nullable|string|max:255',
            'duration' => 'required|numeric|min:0',
            'sessions' => 'required|integer|min:1',
            'selling_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'prerequisites' => 'nullable|string',
            'certificate_name' => 'nullable|boolean',
            'certificate_issuer' => 'nullable|string|max:255',
            'bestseller' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
            'highest_rated' => 'nullable|boolean',
            'status' => 'nullable|boolean',
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
            'title.required' => 'El título del curso es obligatorio',
            'description.required' => 'La descripción del curso es obligatoria',
            'level.required' => 'El nivel del curso es obligatorio',
            'level.in' => 'El nivel debe ser: basic, intermediate o advanced',
            'duration.required' => 'La duración del curso es obligatoria',
            'duration.numeric' => 'La duración debe ser un número',
            'sessions.required' => 'El número de sesiones es obligatorio',
            'sessions.integer' => 'El número de sesiones debe ser un número entero',
            'selling_price.required' => 'El precio de venta es obligatorio',
            'selling_price.numeric' => 'El precio de venta debe ser un número',
        ];
    }
}
