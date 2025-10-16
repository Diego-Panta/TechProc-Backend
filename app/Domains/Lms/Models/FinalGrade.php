<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalGrade extends Model
{
    use HasFactory;

    protected $table = 'final_grades';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'group_id',
        'configuration_id',
        'final_grade',
        'partial_average',
        'program_status',
        'certification_obtained',
        'calculation_date',
    ];

    protected $casts = [
        'final_grade' => 'decimal:2',
        'partial_average' => 'decimal:2',
        'certification_obtained' => 'boolean',
        'calculation_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function configuration()
    {
        return $this->belongsTo(GradeConfiguration::class, 'configuration_id');
    }
}