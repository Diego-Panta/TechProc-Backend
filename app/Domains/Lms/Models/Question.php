<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $table = 'questions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'evaluation_id',
        'statement',
        'question_type',
        'answer_options',
        'correct_answer',
        'score',
    ];

    protected $casts = [
        'answer_options' => 'array',
        'correct_answer' => 'array',
        'score' => 'decimal:2',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id');
    }
}