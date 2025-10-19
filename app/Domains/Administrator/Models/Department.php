<?php

namespace App\Domains\Administrator\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';
    protected $primaryKey = 'id';

    // Deshabilitar timestamps automáticos
    public $timestamps = false;

    protected $fillable = [
        'department_name',
        'description',
    ];

    public function positions()
    {
        return $this->hasMany(Position::class, 'department_id');
    }

    public function jobVacancies()
    {
        return $this->hasMany(JobVacancy::class, 'department_id');
    }
}