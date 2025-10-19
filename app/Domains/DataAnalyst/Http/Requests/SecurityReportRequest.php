<?php

namespace App\Domains\DataAnalyst\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SecurityReportRequest extends FormRequest
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
            'event_type' => 'nullable|string|max:100',
            'severity' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:new,investigating,resolved',
            'ip_address' => 'nullable|ip',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages()
    {
        return [
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
            'severity.in' => 'La severidad debe ser: low, medium o high',
            'status.in' => 'El estado debe ser: new, investigating o resolved',
            'ip_address.ip' => 'La dirección IP debe tener un formato válido',
        ];
    }
}