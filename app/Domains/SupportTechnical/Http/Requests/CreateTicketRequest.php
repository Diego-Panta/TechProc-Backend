<?php

namespace App\Domains\SupportTechnical\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use IncadevUns\CoreDomain\Enums\TicketPriority;
use IncadevUns\CoreDomain\Enums\TicketType;

class CreateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el controlador/middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:' . implode(',', TicketType::values())],
            'priority' => ['required', 'string', 'in:' . implode(',', TicketPriority::values())],
            'content' => ['required', 'string', 'min:10'],
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
            'title.required' => 'El título del ticket es requerido',
            'title.max' => 'El título no puede exceder los 255 caracteres',
            'type.in' => 'El tipo de ticket no es válido',
            'priority.required' => 'La prioridad del ticket es requerida',
            'priority.in' => 'La prioridad seleccionada no es válida',
            'content.required' => 'El contenido del ticket es requerido',
            'content.min' => 'El contenido debe tener al menos 10 caracteres',
        ];
    }
}
