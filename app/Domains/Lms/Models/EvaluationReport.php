<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationReport extends Model
{
    use HasFactory;

    protected $table = 'evaluation_reports';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_evaluation_report',
        'id_instructor',
        'id_instructor_evaluation',
        'id_curse',
        'overall_average',
        'evaluation_period',
        'total_evaluations',
        'generation_date',
    ];

    protected $casts = [
        'overall_average' => 'float',
        'evaluation_period' => 'float',
        'total_evaluations' => 'float',
        'generation_date' => 'datetime',
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'id_instructor');
    }

    public function instructorEvaluation()
    {
        return $this->belongsTo(InstructorEvaluation::class, 'id_instructor_evaluation');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_curse');
    }
}