<?php

namespace App\Domains\SupportTechnical\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:abierto,en_proceso,resuelto,cerrado',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'El estado es obligatorio',
            'status.in' => 'El estado debe ser: abierto, en_proceso, resuelto o cerrado',
        ];
    }
}
