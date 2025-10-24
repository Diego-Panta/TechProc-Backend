<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCourse extends Model
{
    use HasFactory;

    protected $table = 'student_courses';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_student_course',
        'id_student',
        'id_curse',
        'assigned_date',
    ];

    protected $casts = [
        'assigned_date' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'id_student');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_curse');
    }
}