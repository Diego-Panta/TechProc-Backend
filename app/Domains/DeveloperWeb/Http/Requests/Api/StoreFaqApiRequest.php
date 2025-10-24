<?php
// app/Domains/DeveloperWeb/Http/Requests/Api/StoreFaqApiRequest.php

namespace App\Domains\DeveloperWeb\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Domains\DeveloperWeb\Enums\FaqCategory;

class StoreFaqApiRequest extends FormRequest
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
            'category' => 'required|string|in:' . implode(',', FaqCategory::values()),
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
            'category.required' => 'La categoría es obligatoria',
            'category.in' => 'La categoría seleccionada no es válida. Categorías permitidas: ' . implode(', ', FaqCategory::values()),
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

        // Si no se proporciona categoría, usar la por defecto
        if (!$this->has('category') || empty($this->category)) {
            $this->merge([
                'category' => FaqCategory::getDefault()->value
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