<?php

namespace App\Domains\SupportTechnical\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'closing_notes' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'closing_notes.required' => 'Las notas de cierre son obligatorias',
        ];
    }
}
