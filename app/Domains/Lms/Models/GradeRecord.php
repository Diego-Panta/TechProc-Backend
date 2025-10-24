<?php

namespace App\Domains\Lms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Administrator\Models\User;

class GradeRecord extends Model
{
    use HasFactory;

    protected $table = 'grade_records';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'evaluation_id',
        'group_id',
        'configuration_id',
        'obtained_grade',
        'grade_weight',
        'grade_type',
        'status',
        'record_date',
    ];

    protected $casts = [
        'obtained_grade' => 'decimal:2',
        'grade_weight' => 'decimal:2',
        'record_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function configuration()
    {
        return $this->belongsTo(GradeConfiguration::class, 'configuration_id');
    }

    public function gradeChanges()
    {
        return $this->hasMany(GradeChange::class, 'record_id');
    }
}