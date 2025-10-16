<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'enrollments';
    protected $primaryKey = 'id';

    protected $fillable = [
        'enrollment_id',
        'student_id',
        'academic_period_id',
        'enrollment_type',
        'enrollment_date',
        'status',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }

    public function enrollmentDetails()
    {
        return $this->hasMany(EnrollmentDetail::class, 'enrollment_id');
    }
}