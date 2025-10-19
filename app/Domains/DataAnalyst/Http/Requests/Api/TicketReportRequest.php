<?php

namespace App\Domains\DataAnalyst\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class TicketReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Temporalmente sin autenticación
    }

    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category' => 'nullable|string|max:100',
            'priority' => 'nullable|in:baja,media,alta,critica',
            'status' => 'nullable|in:abierto,en_proceso,resuelto,cerrado',
            'technician_id' => 'nullable|exists:employees,id',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            
            // Filtros adicionales para estadísticas
            'limit' => 'nullable|integer|min:1|max:50',
            
            // Filtros avanzados
            'min_resolution_time' => 'nullable|integer|min:0',
            'max_resolution_time' => 'nullable|integer|min:0',
            'has_escalation' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
            'priority.in' => 'La prioridad debe ser: baja, media, alta o critica',
            'status.in' => 'El estado debe ser: abierto, en_proceso, resuelto o cerrado',
            'per_page.max' => 'No se pueden mostrar más de 100 registros por página',
            'limit.max' => 'No se pueden mostrar más de 50 elementos en el ranking',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422));
    }
}