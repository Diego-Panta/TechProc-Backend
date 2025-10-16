<?php

namespace App\Domains\SupportTechnical\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketTracking extends Model
{
    use HasFactory;

    protected $table = 'ticket_trackings';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'ticket_tracking_id',
        'ticket_id',
        'comment',
        'action_type',
        'follow_up_date',
    ];

    protected $casts = [
        'follow_up_date' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
}