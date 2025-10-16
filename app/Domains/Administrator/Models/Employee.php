<?php

namespace App\Domains\Administrator\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';
    protected $primaryKey = 'id';

    // Deshabilitar timestamps automáticos
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'hire_date',
        'position_id',
        'department_id',
        'user_id',
        'employment_status',
        'schedule',
        'speciality',
        'salary',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'responsible_id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_technician');
    }

    public function escalationOrigins()
    {
        return $this->hasMany(Escalation::class, 'technician_origin_id');
    }

    public function escalationDestinies()
    {
        return $this->hasMany(Escalation::class, 'technician_destiny_id');
    }

    public function licenses()
    {
        return $this->hasMany(License::class, 'responsible_id');
    }
}