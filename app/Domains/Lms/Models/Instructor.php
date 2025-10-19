<?php

namespace App\Domains\Lms\Models;

use App\Domains\Administrator\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    use HasFactory;

    protected $table = 'instructors';
    protected $primaryKey = 'id';
    
    // Deshabilitar timestamps automáticos (updated_at no existe en la tabla)
    public $timestamps = false;

    protected $fillable = [
        'instructor_id',
        'user_id',
        'bio',
        'expertise_area',
        'status',
        'created_at',
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