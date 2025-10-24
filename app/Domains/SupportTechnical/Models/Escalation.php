<?php

namespace App\Domains\SupportTechnical\Models;

use App\Domains\Administrator\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Escalation extends Model
{
    use HasFactory;

    protected $table = 'escalations';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'escalation_id',
        'ticket_id',
        'technician_origin_id',
        'technician_destiny_id',
        'escalation_reason',
        'observations',
        'escalation_date',
        'approved',
        'created_at',
    ];

    protected $casts = [
        'escalation_date' => 'datetime',
        'approved' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function technicianOrigin()
    {
        return $this->belongsTo(Employee::class, 'technician_origin_id');
    }

    public function technicianDestiny()
    {
        return $this->belongsTo(Employee::class, 'technician_destiny_id');
    }
}