<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeChange extends Model
{
    use HasFactory;

    protected $table = 'grade_changes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'record_id',
        'previous_grade',
        'new_grade',
        'reason',
        'user_id',
        'change_date',
    ];

    protected $casts = [
        'previous_grade' => 'decimal:2',
        'new_grade' => 'decimal:2',
        'change_date' => 'datetime',
    ];

    public function record()
    {
        return $this->belongsTo(GradeRecord::class, 'record_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}