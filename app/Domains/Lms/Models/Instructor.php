<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    use HasFactory;

    protected $table = 'instructors';
    protected $primaryKey = 'id';

    protected $fillable = [
        'instructor_id',
        'user_id',
        'bio',
        'expertise_area',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function courseInstructors()
    {
        return $this->hasMany(CourseInstructor::class, 'instructor_id');
    }

    public function courseOfferings()
    {
        return $this->hasMany(CourseOffering::class, 'instructor_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_instructors', 'instructor_id', 'course_id');
    }
}