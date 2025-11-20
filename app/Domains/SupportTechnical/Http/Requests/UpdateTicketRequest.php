<?php

namespace App\Domains\SupportTechnical\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use IncadevUns\CoreDomain\Enums\TicketPriority;
use IncadevUns\CoreDomain\Enums\TicketStatus;
use IncadevUns\CoreDomain\Enums\TicketType;

class UpdateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:' . implode(',', TicketStatus::values())],
            'priority' => ['sometimes', 'string', 'in:' . implode(',', TicketPriority::values())],
            'type' => ['sometimes', 'nullable', 'string', 'in:' . implode(',', TicketType::values())],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.max' => 'El título no puede exceder los 255 caracteres',
            'status.in' => 'El estado seleccionado no es válido',
            'priority.in' => 'La prioridad seleccionada no es válida',
            'type.in' => 'El tipo de ticket no es válido',
        ];
    }
}
