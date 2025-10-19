<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseOffering extends Model
{
    use HasFactory;

    protected $table = 'course_offerings';
    protected $primaryKey = 'id';
    
    // Deshabilitar timestamps automáticos
    public $timestamps = false;

    protected $fillable = [
        'course_offering_id',
        'course_id',
        'academic_period_id',
        'instructor_id',
        'schedule',
        'delivery_method',
        'modality',
        'max_capacity',
        'enrolled_count',
        'start_date',
        'end_date',
        'status',
        'created_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    public function enrollmentDetails()
    {
        return $this->hasMany(EnrollmentDetail::class, 'course_offering_id');
    }
}