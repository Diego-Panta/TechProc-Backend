<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationCriterion extends Model
{
    use HasFactory;

    protected $table = 'evaluation_criteria';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_evaluation_criteria',
        'criterion_name',
        'category',
        'response_type',
        'percentage_weight',
        'state',
    ];

    protected $casts = [
        'category' => 'datetime',
        'percentage_weight' => 'float',
    ];

    public function optionCriteria()
    {
        return $this->hasMany(OptionCriterion::class, 'id_evaluation_criteria');
    }

    public function detailEvaluationCriteria()
    {
        return $this->hasMany(DetailEvaluationCriterion::class, 'id_evaluation_criteria');
    }
}