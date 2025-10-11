<?php

namespace App\Domains\SupportSecurity\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityLog extends Model
{
    use HasFactory;

    protected $table = 'security_logs';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_security_log',
        'user_id',
        'event_type',
        'description',
        'source_ip',
        'event_date',
    ];

    protected $casts = [
        'event_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}