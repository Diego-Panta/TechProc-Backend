<?php

namespace App\Domains\DataAnalyst\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class GradeReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Temporalmente sin autenticación
    }

    public function rules(): array
    {
        return [
            'course_id' => 'nullable|integer|exists:courses,id',
            'academic_period_id' => 'nullable|integer|exists:academic_periods,id',
            'grade_type' => 'nullable|in:Partial,Final,Makeup',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.exists' => 'El curso seleccionado no existe',
            'academic_period_id.exists' => 'El período académico seleccionado no existe',
            'grade_type.in' => 'El tipo de calificación debe ser Partial, Final o Makeup',
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
            'limit.max' => 'No se pueden mostrar más de 100 registros por página',
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