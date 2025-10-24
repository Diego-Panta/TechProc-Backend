<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailEvaluationCriterion extends Model
{
    use HasFactory;

    protected $table = 'detail_evaluation_criteria';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_detail_evaluation_criteria',
        'id_evaluation_criteria',
        'id_option_criteria',
        'id_instructor_evaluation',
        'numeric_response',
        'response_text',
    ];

    protected $casts = [
        'numeric_response' => 'float',
    ];

    public function evaluationCriterion()
    {
        return $this->belongsTo(EvaluationCriterion::class, 'id_evaluation_criteria');
    }

    public function optionCriterion()
    {
        return $this->belongsTo(OptionCriterion::class, 'id_option_criteria');
    }

    public function instructorEvaluation()
    {
        return $this->belongsTo(InstructorEvaluation::class, 'id_instructor_evaluation');
    }
}