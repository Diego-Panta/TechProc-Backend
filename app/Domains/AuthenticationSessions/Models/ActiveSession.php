<?php

namespace App\Domains\AuthenticationSessions\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveSession extends Model
{
    use HasFactory;

    protected $table = 'active_sessions';
    protected $primaryKey = 'id';

    // Deshabilitar timestamps ya que la tabla no tiene created_at/updated_at
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'device',
        'start_date',
        'active',
        'blocked',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'active' => 'boolean',
        'blocked' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}