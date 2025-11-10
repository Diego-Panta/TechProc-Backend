<?php

namespace App\Domains\DeveloperWeb\Http\Requests\Api\ContentTypes;

use Illuminate\Foundation\Http\FormRequest;

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
            'status' => 'required|string|in:draft,published,archived,scheduled',
            
            // Campos OPCIONALES para NEWS
            'slug' => 'nullable|string|max:255|unique:content_items,slug',
            'image_url' => 'nullable|url|max:500',
            'item_type' => 'nullable|string|in:article,press-release,update,feature',
            'published_date' => 'nullable|date_format:Y-m-d H:i:s',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ];
    }
}