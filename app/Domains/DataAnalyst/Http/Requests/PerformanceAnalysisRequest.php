<?php
// app/Domains/DataAnalyst/Http/Requests/PerformanceAnalysisRequest.php

namespace App\Domains\DataAnalyst\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PerformanceAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_id' => 'sometimes|integer|exists:groups,id',
            'period' => 'sometimes|string|in:7d,30d,90d,all',
            'refresh' => 'sometimes|boolean',
            'risk_level' => 'sometimes|string|in:all,critical_high,critical,high,medium,low',
            'limit' => 'sometimes|integer|min:1|max:100'
        ];
    }
}