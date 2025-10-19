<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Administrator\Models\User;

class Grading extends Model
{
    use HasFactory;

    protected $table = 'gradings';
    protected $primaryKey = 'id';

    protected $fillable = [
        'attempt_id',
        'teacher_grader_id',
        'grading_detail',
        'feedback',
        'grading_date',
    ];

    protected $casts = [
        'grading_detail' => 'array',
        'grading_date' => 'datetime',
    ];

    public function attempt()
    {
        return $this->belongsTo(Attempt::class, 'attempt_id');
    }

    public function teacherGrader()
    {
        return $this->belongsTo(User::class, 'teacher_grader_id');
    }
}