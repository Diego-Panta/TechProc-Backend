<?php
// app/Domains/DataAnalyst/Http/Requests/RiskAnalysisRequest.php

namespace App\Domains\DataAnalyst\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RiskAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // En producción, agregar políticas de autorización
    }

    public function rules(): array
    {
        return [
            'enrollment_id' => 'sometimes|integer|exists:enrollments,id',
            'risk_level' => 'sometimes|string|in:critical,high,medium,low,none,all',
            'limit' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
            'refresh' => 'sometimes|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'enrollment_id.exists' => 'La matrícula especificada no existe.',
            'risk_level.in' => 'El nivel de riesgo debe ser: critical, high, medium, low, none o all.'
        ];
    }
}