<?php

namespace App\Domains\DataAnalyst\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $table = 'job_applications';
    protected $primaryKey = 'id';

    protected $fillable = [
        'candidate_id',
        'vacancy_id',
        'status',
        'application_date',
    ];

    protected $casts = [
        'application_date' => 'date',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function vacancy()
    {
        return $this->belongsTo(JobVacancy::class, 'vacancy_id');
    }
}