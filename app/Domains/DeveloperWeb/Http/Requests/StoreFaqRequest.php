<?php
// app/Domains/DeveloperWeb/Http/Requests/StoreFaqRequest.php

namespace App\Domains\DeveloperWeb\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => 'required|string|max:1000',
            'answer' => 'required|string|max:5000',
            'category' => 'required_without:new_category|string|max:100|nullable',
            'new_category' => 'required_without:category|string|max:100|nullable',
            'keywords' => 'sometimes|array',
            'keywords.*' => 'string|max:50',
            'active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'La pregunta es obligatoria',
            'answer.required' => 'La respuesta es obligatoria',
            'category.required_without' => 'Debes seleccionar una categoría existente o crear una nueva',
            'new_category.required_without' => 'Debes seleccionar una categoría existente o crear una nueva',
            'keywords.array' => 'Las palabras clave deben ser un array válido',
        ];
    }

    /**
     * Preparar los datos para la validación.
     */
    protected function prepareForValidation()
    {
        // Convertir keywords de JSON string a array si es necesario
        if ($this->has('keywords') && is_string($this->keywords)) {
            try {
                $keywords = json_decode($this->keywords, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->merge(['keywords' => $keywords]);
                } else {
                    $this->merge(['keywords' => []]);
                }
            } catch (\Exception $e) {
                $this->merge(['keywords' => []]);
            }
        }

        // Si se proporciona nueva categoría, usar esa
        if ($this->has('new_category') && !empty($this->new_category)) {
            $this->merge([
                'category' => $this->new_category
            ]);
        }
    }
}