<?php

namespace App\Domains\Lms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseOfferingRequest extends FormRequest
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
            'course_offering_id' => 'nullable|integer',
            'course_id' => 'sometimes|required|integer|exists:courses,id',
            'academic_period_id' => 'sometimes|required|integer|exists:academic_periods,id',
            'instructor_id' => 'nullable|integer|exists:instructors,id',
            'schedule' => 'nullable|string',
            'delivery_method' => 'nullable|string|max:50',
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
            'course_id.required' => 'El ID del curso es obligatorio',
            'course_id.exists' => 'El curso especificado no existe',
            'academic_period_id.required' => 'El ID del período académico es obligatorio',
            'academic_period_id.exists' => 'El período académico especificado no existe',
            'instructor_id.exists' => 'El instructor especificado no existe',
            'delivery_method.max' => 'El método de entrega no puede exceder 50 caracteres',
        ];
    }
}
