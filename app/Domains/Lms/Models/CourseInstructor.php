<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseInstructor extends Model
{
    use HasFactory;

    protected $table = 'course_instructors';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_course_inst',
        'instructor_id',
        'course_id',
        'assigned_date',
    ];

    protected $casts = [
        'assigned_date' => 'datetime',
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}