<?php

namespace App\Domains\DataAnalyst\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CourseReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Temporalmente sin autenticación
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
            'level' => 'nullable|in:basic,intermediate,advanced',
            'status' => 'nullable|boolean',
            'bestseller' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
            'highest_rated' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            
            // Filtros para estadísticas
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'min_duration' => 'nullable|numeric|min:0',
            'max_duration' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'level.in' => 'El nivel debe ser basic, intermediate o advanced',
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
            'per_page.max' => 'No se pueden mostrar más de 100 registros por página',
            'category_id.exists' => 'La categoría seleccionada no existe',
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