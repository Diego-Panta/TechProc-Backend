<?php

namespace App\Domains\DataAnalyst\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class FinancialReportRequest extends FormRequest
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
            'revenue_source_id' => 'nullable|exists:revenue_sources,id',
            'period' => 'nullable|in:daily,weekly,monthly,quarterly,yearly',
            
            // Paginación
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            
            // Filtros avanzados
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'invoice_status' => 'nullable|in:Pending,Paid,Cancelled',
            'payment_status' => 'nullable|in:Pending,Completed,Failed',
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
            'period.in' => 'El período debe ser: daily, weekly, monthly, quarterly o yearly',
            'revenue_source_id.exists' => 'La fuente de ingresos seleccionada no existe',
            'per_page.max' => 'No se pueden mostrar más de 100 registros por página',
            'invoice_status.in' => 'El estado de factura no es válido',
            'payment_status.in' => 'El estado de pago no es válido',
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