<?php

namespace App\Domains\DeveloperWeb\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateNewsApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Temporalmente sin autenticación
    }

    public function rules(): array
    {
        $newsId = $this->route('id');

        return [
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:news,slug,' . $newsId,
            'summary' => 'sometimes|string|min:10|max:500',
            'content' => 'sometimes|string|min:50',
            'featured_image' => 'nullable|url|max:500',
            'author_id' => 'nullable|exists:users,id',
            'category' => 'sometimes|string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'status' => 'sometimes|string|in:draft,published,archived',
            'published_date' => 'nullable|date',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'summary.min' => 'El resumen debe tener al menos 10 caracteres',
            'summary.max' => 'El resumen no debe exceder los 500 caracteres',
            'content.min' => 'El contenido debe tener al menos 50 caracteres',
            'slug.unique' => 'Este slug ya está en uso',
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