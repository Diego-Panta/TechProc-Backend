<?php

namespace App\Domains\Security\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class UserSession extends Model
{
    /**
     * Mapeo a la tabla sessions de Laravel
     */
    protected $table = 'sessions';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity',
    ];

    protected $casts = [
        'last_activity' => 'integer',
    ];

    /**
     * Relación con usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener información del dispositivo desde user_agent
     */
    public function getDeviceAttribute(): string
    {
        $agent = $this->user_agent;

        // Detectar navegador
        if (str_contains($agent, 'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($agent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($agent, 'Safari')) {
            $browser = 'Safari';
        } elseif (str_contains($agent, 'Edge')) {
            $browser = 'Edge';
        } else {
            $browser = 'Unknown';
        }

        // Detectar SO
        if (str_contains($agent, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($agent, 'Mac')) {
            $os = 'MacOS';
        } elseif (str_contains($agent, 'Linux')) {
            $os = 'Linux';
        } elseif (str_contains($agent, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($agent, 'iPhone') || str_contains($agent, 'iPad')) {
            $os = 'iOS';
        } else {
            $os = 'Unknown';
        }

        return "$browser on $os";
    }

    /**
     * Verificar si la sesión está activa (últimos 30 minutos)
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->last_activity >= (time() - 1800); // 30 minutos
    }

    /**
     * Obtener tiempo desde última actividad
     */
    public function getLastActivityHumanAttribute(): string
    {
        $diff = time() - $this->last_activity;

        if ($diff < 60) {
            return 'Hace menos de 1 minuto';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return "Hace $mins " . ($mins == 1 ? 'minuto' : 'minutos');
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "Hace $hours " . ($hours == 1 ? 'hora' : 'horas');
        } else {
            $days = floor($diff / 86400);
            return "Hace $days " . ($days == 1 ? 'día' : 'días');
        }
    }

    /**
     * Scope: Sesiones activas
     */
    public function scopeActive($query)
    {
        return $query->where('last_activity', '>=', time() - 1800);
    }

    /**
     * Scope: Por usuario
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Por IP
     */
    public function scopeFromIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Terminar esta sesión
     */
    public function terminate(): bool
    {
        return DB::table($this->table)->where('id', $this->id)->delete() > 0;
    }
}
