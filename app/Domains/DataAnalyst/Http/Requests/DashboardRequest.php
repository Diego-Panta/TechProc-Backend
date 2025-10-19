<?php

namespace App\Domains\DataAnalyst\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
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
            'academic_period_id' => 'nullable|exists:academic_periods,id',
            'company_id' => 'nullable|exists:companies,id',
        ];
    }

    public function messages()
    {
        return [
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
        ];
    }
}