<?php

namespace App\Domains\DataAnalyst\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ExportReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Temporalmente sin autenticación
    }

    public function rules(): array
    {
        return [
            'report_type' => 'required|in:students,courses,attendance,grades,financial,tickets,security,dashboard',
            'format' => 'required|in:excel,pdf',
            'report_title' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'filters' => 'nullable|array',
            'filters.company_id' => 'nullable|exists:companies,id',
            'filters.academic_period_id' => 'nullable|exists:academic_periods,id',
            'filters.status' => 'nullable|string',
            'filters.start_date' => 'nullable|date',
            'filters.end_date' => 'nullable|date|after_or_equal:filters.start_date',
            'include_charts' => 'nullable|boolean',
            'include_raw_data' => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'report_type.required' => 'El tipo de reporte es obligatorio',
            'report_type.in' => 'El tipo de reporte no es válido',
            'format.required' => 'El formato es obligatorio',
            'format.in' => 'El formato debe ser excel o pdf',
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
            'filters.end_date.after_or_equal' => 'La fecha final del filtro debe ser igual o posterior a la fecha inicial',
        ];
    }

    public function attributes(): array
    {
        return [
            'filters.company_id' => 'empresa',
            'filters.academic_period_id' => 'período académico',
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