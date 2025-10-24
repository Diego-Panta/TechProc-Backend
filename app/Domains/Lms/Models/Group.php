<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';
    protected $primaryKey = 'id';

    protected $fillable = [
        'course_id',
        'code',
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function groupParticipants()
    {
        return $this->hasMany(GroupParticipant::class, 'group_id');
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'group_id');
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'group_id');
    }

    public function gradeConfigurations()
    {
        return $this->hasOne(GradeConfiguration::class, 'group_id');
    }

    public function gradeRecords()
    {
        return $this->hasMany(GradeRecord::class, 'group_id');
    }

    public function finalGrades()
    {
        return $this->hasMany(FinalGrade::class, 'group_id');
    }
}