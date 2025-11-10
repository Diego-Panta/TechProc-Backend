<?php
// app/Domains/DataAnalyst/Http/Requests/ProgressAnalysisRequest.php

namespace App\Domains\DataAnalyst\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgressAnalysisRequest extends FormRequest
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
            'period' => 'sometimes|string|in:7d,30d,90d,all',
            'refresh' => 'sometimes|boolean'
        ];
    }
}