<?php

namespace App\Domains\DataAnalyst\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $table = 'candidates';
    protected $primaryKey = 'id';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'cv_path',
    ];

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class, 'candidate_id');
    }
}