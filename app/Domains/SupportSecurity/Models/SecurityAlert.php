<?php

namespace App\Domains\SupportSecurity\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityAlert extends Model
{
    use HasFactory;

    protected $table = 'security_alerts';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_security_alert',
        'threat_type',
        'severity',
        'status',
        'blocked_ip_id',
        'detection_date',
    ];

    protected $casts = [
        'detection_date' => 'datetime',
    ];

    public function blockedIp()
    {
        return $this->belongsTo(BlockedIp::class, 'blocked_ip_id');
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'alert_id');
    }
}