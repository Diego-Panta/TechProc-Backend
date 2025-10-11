<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OptionCriterion extends Model
{
    use HasFactory;

    protected $table = 'option_criteria';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_option_criteria',
        'id_evaluation_criteria',
        'option_text',
    ];

    public function evaluationCriterion()
    {
        return $this->belongsTo(EvaluationCriterion::class, 'id_evaluation_criteria');
    }

    public function detailEvaluationCriteria()
    {
        return $this->hasMany(DetailEvaluationCriterion::class, 'id_option_criteria');
    }
}