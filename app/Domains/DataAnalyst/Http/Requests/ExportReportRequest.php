<?php

namespace App\Domains\DataAnalyst\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'report_type' => 'required|in:students,courses,attendance,grades,financial,tickets,security,dashboard',
            'format' => 'required|in:excel,pdf',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'filters' => 'nullable|array',
            'filters.start_date' => 'nullable|date',
            'filters.end_date' => 'nullable|date|after_or_equal:filters.start_date',
            'filters.company_id' => 'nullable|exists:companies,id',
            'filters.academic_period_id' => 'nullable|exists:academic_periods,id',
            'filters.course_id' => 'nullable|exists:courses,id',
            'filters.status' => 'nullable|string',
            'include_charts' => 'nullable|boolean',
            'include_raw_data' => 'nullable|boolean',
            'report_title' => 'nullable|string|max:255'
        ];
    }

    public function messages()
    {
        return [
            'report_type.required' => 'El tipo de reporte es obligatorio',
            'report_type.in' => 'El tipo de reporte no es válido',
            'format.required' => 'El formato es obligatorio',
            'format.in' => 'El formato debe ser excel o pdf',
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
        ];
    }

    public function attributes()
    {
        return [
            'filters.company_id' => 'empresa',
            'filters.academic_period_id' => 'período académico',
            'filters.course_id' => 'curso',
        ];
    }
}