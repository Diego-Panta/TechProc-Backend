<?php

namespace App\Domains\SupportTechnical\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Administrator\Models\User;
use App\Domains\Administrator\Models\Employee;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'tickets';
    protected $primaryKey = 'id';

    protected $fillable = [
        'ticket_id',
        'assigned_technician',
        'user_id',
        'title',
        'description',
        'priority',
        'status',
        'creation_date',
        'assignment_date',
        'resolution_date',
        'close_date',
        'category',
        'notes',
    ];

    protected $casts = [
        'creation_date' => 'datetime',
        'assignment_date' => 'datetime',
        'resolution_date' => 'datetime',
        'close_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTechnician()
    {
        return $this->belongsTo(Employee::class, 'assigned_technician');
    }

    public function ticketTrackings()
    {
        return $this->hasMany(TicketTracking::class, 'ticket_id');
    }

    public function escalations()
    {
        return $this->hasMany(Escalation::class, 'ticket_id');
    }
}