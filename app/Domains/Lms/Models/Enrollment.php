<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'enrollments';
    protected $primaryKey = 'id';
    
    // Deshabilitar timestamps automáticos
    public $timestamps = false;

    protected $fillable = [
        'enrollment_id',
        'student_id',
        'academic_period_id',
        'enrollment_type',
        'enrollment_date',
        'status',
<<<<<<< HEAD
        'created_at',
=======
>>>>>>> 8a7a4e09f73c188c87f63e811616322463c07950
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