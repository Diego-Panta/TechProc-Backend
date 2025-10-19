<?php

namespace App\Domains\DataAnalyst\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Reglas diferentes para diferentes endpoints
        if ($this->routeIs('dataanalyst.grades.groups-by-course')) {
            return [
                'course_id' => 'nullable|integer|exists:courses,id'
            ];
        }

        return [
            'course_id' => 'nullable|integer|exists:courses,id',
            'academic_period_id' => 'nullable|integer|exists:academic_periods,id',
            'grade_type' => 'nullable|in:Partial,Final,Makeup',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'limit' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages()
    {
        return [
            'course_id.exists' => 'El curso seleccionado no existe',
            'academic_period_id.exists' => 'El período académico seleccionado no existe',
            'grade_type.in' => 'El tipo de calificación debe ser Partial, Final o Makeup',
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
        ];
    }
}