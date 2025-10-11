<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $table = 'evaluations';
    protected $primaryKey = 'id';

    protected $fillable = [
        'group_id',
        'title',
        'evaluation_type',
        'start_date',
        'end_date',
        'duration_minutes',
        'total_score',
        'status',
        'teacher_creator_id',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'duration_minutes' => 'integer',
        'total_score' => 'decimal:2',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function teacherCreator()
    {
        return $this->belongsTo(User::class, 'teacher_creator_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'evaluation_id');
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class, 'evaluation_id');
    }

    public function gradeRecords()
    {
        return $this->hasMany(GradeRecord::class, 'evaluation_id');
    }
}