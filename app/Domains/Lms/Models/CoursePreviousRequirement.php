<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursePreviousRequirement extends Model
{
    use HasFactory;

    protected $table = 'course_previous_requirements';
    protected $primaryKey = 'id';

    protected $fillable = [
        'course_id',
        'previous_course_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function previousCourse()
    {
        return $this->belongsTo(Course::class, 'previous_course_id');
    }
}