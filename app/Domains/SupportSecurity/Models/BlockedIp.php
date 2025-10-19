<?php

namespace App\Domains\SupportSecurity\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    use HasFactory;

    protected $table = 'blocked_ips';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_blocked_ip',
        'ip_address',
        'reason',
        'block_date',
        'active',
    ];

    protected $casts = [
        'block_date' => 'datetime',
        'active' => 'boolean',
    ];

    public function securityAlerts()
    {
        return $this->hasMany(SecurityAlert::class, 'blocked_ip_id');
    }
}