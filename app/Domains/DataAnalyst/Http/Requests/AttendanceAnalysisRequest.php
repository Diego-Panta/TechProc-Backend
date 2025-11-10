<?php
// app/Domains/DataAnalyst/Http/Requests/AttendanceAnalysisRequest.php

namespace App\Domains\DataAnalyst\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enrollment_id' => 'sometimes|integer|exists:enrollments,id',
            'group_id' => 'sometimes|integer|exists:groups,id',
            'risk_level' => 'sometimes|string|in:critical,high,medium,low,none,all',
            'period' => 'sometimes|string|in:7d,30d,90d,all',
            'limit' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
            'refresh' => 'sometimes|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'enrollment_id.exists' => 'La matrícula especificada no existe.',
            'group_id.exists' => 'El grupo especificado no existe.',
            'risk_level.in' => 'El nivel de riesgo debe ser: critical, high, medium, low, none o all.',
            'period.in' => 'El período debe ser: 7d, 30d, 90d o all.'
        ];
    }
}