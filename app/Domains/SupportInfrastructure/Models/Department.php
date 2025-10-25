<?php

namespace App\Domains\SupportInfrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'department_name',
        'description',
        'created_at',
        'updated_at'
    ];

    // Relación con employees
    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }
}
