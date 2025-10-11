<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorEvaluation extends Model
{
    use HasFactory;

    protected $table = 'instructor_evaluations';
    protected $primaryKey = 'id';

    protected $fillable = [
        'instructor_evaluation_id',
        'student_id',
        'instructor_id',
        'course_offering_id',
        'rating',
        'feedback',
        'evaluation_period',
        'evaluation_status',
        'evaluation_date',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'evaluation_date' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    public function courseOffering()
    {
        return $this->belongsTo(CourseOffering::class, 'course_offering_id');
    }

    public function detailEvaluationCriteria()
    {
        return $this->hasMany(DetailEvaluationCriterion::class, 'id_instructor_evaluation');
    }

    public function evaluationReports()
    {
        return $this->hasMany(EvaluationReport::class, 'id_instructor_evaluation');
    }
}