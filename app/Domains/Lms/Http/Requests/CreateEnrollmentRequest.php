<?php

namespace App\Domains\Lms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEnrollmentRequest extends FormRequest
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
            'student_id' => 'required|integer|exists:students,id',
            'academic_period_id' => 'required|integer|exists:academic_periods,id',
            'course_offering_ids' => 'required|array|min:1',
            'course_offering_ids.*' => 'integer|exists:course_offerings,id',
            'enrollment_type' => 'required|string|in:new,renewal,transfer',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:active,inactive,completed,cancelled',
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
            'student_id.required' => 'El ID del estudiante es obligatorio',
            'student_id.exists' => 'El estudiante especificado no existe',
            'academic_period_id.required' => 'El período académico es obligatorio',
            'academic_period_id.exists' => 'El período académico especificado no existe',
            'course_offering_ids.required' => 'Debe especificar al menos un curso',
            'course_offering_ids.*.exists' => 'Una o más ofertas de curso no existen',
            'enrollment_type.required' => 'El tipo de matrícula es obligatorio',
            'enrollment_type.in' => 'El tipo de matrícula debe ser: new, renewal o transfer',
            'enrollment_date.required' => 'La fecha de matrícula es obligatoria',
            'enrollment_date.date' => 'La fecha de matrícula debe ser una fecha válida',
            'status.required' => 'El estado es obligatorio',
            'status.in' => 'El estado debe ser: active, inactive, completed o cancelled',
        ];
    }
}
