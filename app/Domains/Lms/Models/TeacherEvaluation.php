<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Administrator\Models\User;
use App\Domains\Lms\Models\Group;

class TeacherEvaluation extends Model
{
    use HasFactory;

    protected $table = 'teacher_evaluations';
    protected $primaryKey = 'id';

    protected $fillable = [
        'evaluator_id',
        'group_id',
        'teacher_id',
        'answers',
        'score',
    ];

    protected $casts = [
        'answers' => 'array',
        'score' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}