<?php

namespace App\Domains\Lms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // TODO: Implementar lógica de autorización cuando el módulo de autenticación esté disponible
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category_id');

        return [
            'name' => 'sometimes|string|max:100',
            'slug' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('categories', 'slug')->ignore($categoryId)
            ],
            'image' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
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
            'name.string' => 'El nombre debe ser una cadena de texto',
            'name.max' => 'El nombre no puede exceder 100 caracteres',
            'slug.string' => 'El slug debe ser una cadena de texto',
            'slug.max' => 'El slug no puede exceder 100 caracteres',
            'slug.unique' => 'Este slug ya está en uso',
            'image.max' => 'La URL de la imagen no puede exceder 255 caracteres',
            'category_id.exists' => 'La categoría padre no existe',
        ];
    }
}
