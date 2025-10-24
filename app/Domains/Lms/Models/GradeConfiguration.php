<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeConfiguration extends Model
{
    use HasFactory;

    protected $table = 'grade_configurations';
    protected $primaryKey = 'id';

    protected $fillable = [
        'group_id',
        'grading_system',
        'max_grade',
        'passing_grade',
        'evaluation_weight',
    ];

    protected $casts = [
        'max_grade' => 'decimal:2',
        'passing_grade' => 'decimal:2',
        'evaluation_weight' => 'decimal:2',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function gradeRecords()
    {
        return $this->hasMany(GradeRecord::class, 'configuration_id');
    }

    public function finalGrades()
    {
        return $this->hasMany(FinalGrade::class, 'configuration_id');
    }
}