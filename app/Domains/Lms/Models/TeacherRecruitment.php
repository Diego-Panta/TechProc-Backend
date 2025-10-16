<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherRecruitment extends Model
{
    use HasFactory;

    protected $table = 'teacher_recruitments';
    protected $primaryKey = 'id';

    protected $fillable = [
        'request_date',
        'title',
        'description',
        'required_profile',
        'status',
    ];

    protected $casts = [
        'request_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function teacherApplications()
    {
        return $this->hasMany(TeacherApplication::class, 'recruitment_id');
    }
}