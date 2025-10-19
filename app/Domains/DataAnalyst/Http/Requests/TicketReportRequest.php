<?php

namespace App\Domains\DataAnalyst\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketReportRequest extends FormRequest
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
            'category' => 'nullable|string|max:100',
            'priority' => 'nullable|in:baja,media,alta,critica',
            'status' => 'nullable|in:abierto,en_proceso,resuelto,cerrado',
            'technician_id' => 'nullable|exists:employees,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages()
    {
        return [
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
            'priority.in' => 'La prioridad debe ser: baja, media, alta o critica',
            'status.in' => 'El estado debe ser: abierto, en_proceso, resuelto o cerrado',
        ];
    }
}