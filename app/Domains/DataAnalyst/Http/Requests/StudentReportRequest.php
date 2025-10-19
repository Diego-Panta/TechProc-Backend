<?php

namespace App\Domains\DataAnalyst\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:1|max:100',
            'company_id' => 'nullable|exists:companies,id',
            'academic_period_id' => 'nullable|exists:academic_periods,id',
        ];
    }

    public function messages()
    {
        return [
            'status.in' => 'El estado debe ser active o inactive',
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
        ];
    }
}