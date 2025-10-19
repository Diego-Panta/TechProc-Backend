<?php

namespace App\Domains\DataAnalyst\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
            'level' => 'nullable|in:basic,intermediate,advanced',
            'status' => 'nullable|boolean',
            'bestseller' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages()
    {
        return [
            'level.in' => 'El nivel debe ser basic, intermediate o advanced',
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
        ];
    }
}