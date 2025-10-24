<?php

namespace App\Domains\SupportTechnical\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EscalateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'technician_origin_id' => 'required|integer|exists:employees,id',
            'technician_destiny_id' => 'required|integer|exists:employees,id|different:technician_origin_id',
            'escalation_reason' => 'required|string',
            'observations' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'technician_origin_id.required' => 'El técnico de origen es obligatorio',
            'technician_origin_id.exists' => 'El técnico de origen no existe',
            'technician_destiny_id.required' => 'El técnico de destino es obligatorio',
            'technician_destiny_id.exists' => 'El técnico de destino no existe',
            'technician_destiny_id.different' => 'El técnico de destino debe ser diferente al de origen',
            'escalation_reason.required' => 'La razón de escalación es obligatoria',
        ];
    }
}
