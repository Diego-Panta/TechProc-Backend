<?php

namespace App\Domains\DataAnalyst\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancy extends Model
{
    use HasFactory;

    protected $table = 'job_vacancies';
    protected $primaryKey = 'id';

    protected $fillable = [
        'position_id',
        'department_id',
        'status',
        'posted_date',
        'description',
        'requirements',
        'salary_range',
    ];

    protected $casts = [
        'posted_date' => 'date',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class, 'vacancy_id');
    }
}