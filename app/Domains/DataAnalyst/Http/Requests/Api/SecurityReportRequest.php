<?php

namespace App\Domains\DataAnalyst\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class SecurityReportRequest extends FormRequest
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
            'event_type' => 'nullable|string|max:100',
            'severity' => 'nullable|in:low,medium,high,critical',
            'status' => 'nullable|in:new,investigating,resolved',
            'ip_address' => 'nullable|ip',
            'threat_type' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
            'severity.in' => 'La severidad debe ser: low, medium, high o critical',
            'status.in' => 'El estado debe ser: new, investigating o resolved',
            'ip_address.ip' => 'La dirección IP debe tener un formato válido',
            'per_page.max' => 'No se pueden mostrar más de 100 registros por página',
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