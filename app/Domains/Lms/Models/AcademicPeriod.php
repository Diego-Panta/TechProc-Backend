<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicPeriod extends Model
{
    use HasFactory;

    protected $table = 'academic_periods';
    protected $primaryKey = 'id';

    protected $fillable = [
        'academic_period_id',
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function courseOfferings()
    {
        return $this->hasMany(CourseOffering::class, 'academic_period_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'academic_period_id');
    }
}