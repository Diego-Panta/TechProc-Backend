<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    use HasFactory;

    protected $table = 'attempts';
    protected $primaryKey = 'id';

    protected $fillable = [
        'evaluation_id',
        'user_id',
        'start_date',
        'end_date',
        'answers',
        'obtained_score',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'answers' => 'array',
        'obtained_score' => 'decimal:2',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grading()
    {
        return $this->hasOne(Grading::class, 'attempt_id');
    }
}