<?php

namespace App\Domains\DataAnalyst\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinancialReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'revenue_source_id' => 'nullable|exists:revenue_sources,id',
            'period' => 'nullable|in:daily,weekly,monthly,quarterly,yearly',
        ];
    }

    public function messages()
    {
        return [
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
            'period.in' => 'El período debe ser: daily, weekly, monthly, quarterly o yearly',
        ];
    }
}