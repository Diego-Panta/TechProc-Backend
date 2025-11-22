<?php

namespace App\Domains\DeveloperWeb\Http\Requests\ContentTypes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Domains\DeveloperWeb\Enums\ContentStatus;
use App\Domains\DeveloperWeb\Enums\NewsItemType;
use App\Domains\DeveloperWeb\Enums\NewsCategory;

class UpdateNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $newsId = $this->route('id');

        return [
            // Campos OPCIONALES para actualización de NEWS
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:content_items,slug,' . $newsId,
            'summary' => 'sometimes|string|min:10|max:500',
            'content' => 'sometimes|string|min:50',
            'image_url' => 'nullable|url|max:500',
            'category' => 'sometimes|string|in:' . implode(',', NewsCategory::all()),
            'item_type' => 'nullable|string|in:' . implode(',', NewsItemType::all()),
            'status' => 'sometimes|string|in:' . implode(',', ContentStatus::forNews()),
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
            'title.string' => 'El título debe ser texto',
            'title.max' => 'El título no debe exceder 255 caracteres',
            'slug.unique' => 'Este slug ya está en uso',
            'summary.min' => 'El resumen debe tener al menos 10 caracteres',
            'summary.max' => 'El resumen no debe exceder los 500 caracteres',
            'content.min' => 'El contenido debe tener al menos 50 caracteres',
            'category.in' => 'La categoría seleccionada no es válida',
            'published_date.date_format' => 'La fecha debe tener el formato Y-m-d H:i:s',
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