<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentDetail extends Model
{
    use HasFactory;

    protected $table = 'enrollment_details';
    protected $primaryKey = 'id';

    protected $fillable = [
        'enrollment_id',
        'subject_id',
        'course_offering_id',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function courseOffering()
    {
        return $this->belongsTo(CourseOffering::class, 'course_offering_id');
    }
}