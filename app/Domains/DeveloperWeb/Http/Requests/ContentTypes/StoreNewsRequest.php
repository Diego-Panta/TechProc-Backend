<?php

namespace App\Domains\DeveloperWeb\Http\Requests\ContentTypes;

use Illuminate\Foundation\Http\FormRequest;
use App\Domains\DeveloperWeb\Enums\ContentStatus;
use App\Domains\DeveloperWeb\Enums\NewsItemType;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Campos REQUERIDOS para NEWS
            'title' => 'required|string|max:255',
            'summary' => 'required|string|min:10|max:500',
            'content' => 'required|string|min:50',
            'category' => 'required|string|max:100',
            'status' => 'required|string|in:' . implode(',', ContentStatus::forNews()),
            
            // Campos OPCIONALES para NEWS
            'slug' => 'nullable|string|max:255|unique:content_items,slug',
            'image_url' => 'nullable|url|max:500',
            'item_type' => 'nullable|string|in:' . implode(',', NewsItemType::all()),
            'published_date' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:today',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
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
            'published_date.date_format' => 'La fecha debe tener el formato Y-m-d H:i:s (ej: 2024-01-20 10:00:00)',
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
            'message' => 'Los datos proporcionados no son válidos.',
            'errors' => $validator->errors()
        ], 422));
    }
}