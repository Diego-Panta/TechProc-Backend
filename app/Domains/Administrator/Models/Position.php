<?php

namespace App\Domains\Administrator\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\DataAnalyst\Models\JobVacancy;
use App\Domains\SupportInfrastructure\Models\Department;
use App\Domains\SupportInfrastructure\Models\Employee;

class Position extends Model
{
    use HasFactory;

    protected $table = 'positions';
    protected $primaryKey = 'id';

    // Deshabilitar timestamps automáticos
    public $timestamps = false;

    protected $fillable = [
        'id',
        'position_name',
        'department_id',
        'created_at',
        'updated_at'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'position_id');
    }

    public function jobVacancies()
    {
        return $this->hasMany(JobVacancy::class, 'position_id');
    }
}