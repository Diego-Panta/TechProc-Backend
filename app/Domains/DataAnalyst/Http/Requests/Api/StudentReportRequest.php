<?php

namespace App\Domains\DataAnalyst\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StudentReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Temporalmente sin autenticación
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            
            // Filtros para estadísticas
            'company_id' => 'nullable|exists:companies,id',
            'academic_period_id' => 'nullable|exists:academic_periods,id',
            
            // Filtros avanzados
            'enrollment_status' => 'nullable|in:active,inactive,completed,cancelled',
            'min_enrollments' => 'nullable|integer|min:0',
            'max_enrollments' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'El estado debe ser active o inactive',
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
            'per_page.max' => 'No se pueden mostrar más de 100 registros por página',
            'enrollment_status.in' => 'El estado de matrícula no es válido',
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