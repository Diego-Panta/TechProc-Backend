<?php

namespace App\Domains\Administrator\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $table = 'positions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'position_name',
        'department_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function jobVacancies()
    {
        return $this->hasMany(JobVacancy::class, 'position_id');
    }
}