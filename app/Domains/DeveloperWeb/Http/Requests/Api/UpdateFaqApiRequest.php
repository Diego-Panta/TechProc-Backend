<?php
// app/Domains/DeveloperWeb/Http/Requests/Api/UpdateFaqApiRequest.php

namespace App\Domains\DeveloperWeb\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateFaqApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Temporalmente sin autenticación
    }

    public function rules(): array
    {
        return [
            'question' => 'sometimes|string|max:1000',
            'answer' => 'sometimes|string|max:5000',
            'category' => 'sometimes|string|max:100|nullable',
            'new_category' => 'sometimes|string|max:100|nullable',
            'keywords' => 'sometimes|array',
            'keywords.*' => 'string|max:50',
            'active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
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

        // Si no se proporciona ninguna categoría, mantener la existente
        if (!$this->has('category') && !$this->has('new_category')) {
            $this->merge([
                'category' => null // Esto permitirá que se mantenga la categoría actual
            ]);
        }
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