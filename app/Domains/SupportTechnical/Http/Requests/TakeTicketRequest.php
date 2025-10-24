<?php

namespace App\Domains\SupportTechnical\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TakeTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'technician_id' => 'required|integer|exists:employees,id',
        ];
    }

    public function messages(): array
    {
        return [
            'technician_id.required' => 'El ID del técnico es obligatorio',
            'technician_id.exists' => 'El técnico especificado no existe',
        ];
    }
}
