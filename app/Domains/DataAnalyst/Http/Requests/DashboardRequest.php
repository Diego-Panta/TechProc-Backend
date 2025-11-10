<?php

namespace App\Domains\DataAnalyst\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Temporalmente sin autenticación
    }

    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'academic_period_id' => 'nullable|exists:academic_periods,id',
            'company_id' => 'nullable|exists:companies,id',
            'limit' => 'nullable|integer|min:1|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
            'limit.max' => 'No se pueden mostrar más de 20 actividades',
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