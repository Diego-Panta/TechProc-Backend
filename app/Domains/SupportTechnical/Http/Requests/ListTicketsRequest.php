<?php

namespace App\Domains\SupportTechnical\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use IncadevUns\CoreDomain\Enums\TicketPriority;
use IncadevUns\CoreDomain\Enums\TicketStatus;
use IncadevUns\CoreDomain\Enums\TicketType;

class ListTicketsRequest extends FormRequest
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
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'string', 'in:' . implode(',', TicketStatus::values())],
            'priority' => ['sometimes', 'string', 'in:' . implode(',', TicketPriority::values())],
            'type' => ['sometimes', 'string', 'in:' . implode(',', TicketType::values())],
            'search' => ['sometimes', 'string', 'min:3'],
            'sort_by' => ['sometimes', 'string', 'in:created_at,updated_at,priority'],
            'sort_order' => ['sometimes', 'string', 'in:asc,desc'],
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
            'per_page.max' => 'No se pueden solicitar más de 100 tickets por página',
            'search.min' => 'La búsqueda debe tener al menos 3 caracteres',
            'status.in' => 'El estado seleccionado no es válido',
            'priority.in' => 'La prioridad seleccionada no es válida',
            'type.in' => 'El tipo de ticket no es válido',
            'sort_by.in' => 'El campo de ordenamiento no es válido',
            'sort_order.in' => 'El orden debe ser asc o desc',
        ];
    }
}
