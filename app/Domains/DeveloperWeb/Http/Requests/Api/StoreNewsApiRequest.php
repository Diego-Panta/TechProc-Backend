<?php

namespace App\Domains\DeveloperWeb\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreNewsApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Temporalmente sin autenticación
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:news,slug',
            'summary' => 'required|string|min:10|max:500',
            'content' => 'required|string|min:50',
            'featured_image' => 'nullable|url|max:500',
            'author_id' => 'nullable|exists:users,id',
            'category' => 'required|string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'status' => 'required|string|in:draft,published,archived',
            'published_date' => 'nullable|date|after_or_equal:today',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio',
            'summary.required' => 'El resumen es obligatorio',
            'summary.min' => 'El resumen debe tener al menos 10 caracteres',
            'summary.max' => 'El resumen no debe exceder los 500 caracteres',
            'content.required' => 'El contenido es obligatorio',
            'content.min' => 'El contenido debe tener al menos 50 caracteres',
            'category.required' => 'La categoría es obligatoria',
            'status.required' => 'El estado es obligatorio',
            'slug.unique' => 'Este slug ya está en uso',
            'published_date.after_or_equal' => 'La fecha de publicación no puede ser en el pasado',
        ];
    }

    public function prepareForValidation()
    {
        if ($this->has('tags') && is_string($this->tags)) {
            $this->merge([
                'tags' => json_decode($this->tags, true) ?? []
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