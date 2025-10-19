<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherApplication extends Model
{
    use HasFactory;

    protected $table = 'teacher_applications';
    protected $primaryKey = 'id';

    protected $fillable = [
        'recruitment_id',
        'user_id',
        'cv',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function recruitment()
    {
        return $this->belongsTo(TeacherRecruitment::class, 'recruitment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}