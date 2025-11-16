<?php

namespace App\Domains\Security\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\PersonalAccessToken;

class ActiveToken extends PersonalAccessToken
{
    /**
     * Especificar tabla correcta
     */
    protected $table = 'personal_access_tokens';

    /**
     * Relación con usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tokenable_id');
    }

    /**
     * Obtener información del dispositivo desde abilities metadata
     */
    public function getDeviceAttribute(): string
    {
        $abilities = $this->abilities;

        if (is_array($abilities) && isset($abilities['user_agent'])) {
            $agent = $abilities['user_agent'];

            // Detectar navegador
            if (str_contains($agent, 'Chrome')) {
                $browser = 'Chrome';
            } elseif (str_contains($agent, 'Firefox')) {
                $browser = 'Firefox';
            } elseif (str_contains($agent, 'Safari')) {
                $browser = 'Safari';
            } elseif (str_contains($agent, 'Edge')) {
                $browser = 'Edge';
            } elseif (str_contains($agent, 'Postman')) {
                $browser = 'Postman';
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

        return 'Unknown Device';
    }

    /**
     * Obtener IP desde abilities metadata
     */
    public function getIpAddressAttribute(): ?string
    {
        $abilities = $this->abilities;
        return is_array($abilities) && isset($abilities['ip']) ? $abilities['ip'] : null;
    }

    /**
     * Verificar si el token está activo (usado recientemente)
     */
    public function getIsActiveAttribute(): bool
    {
        if (!$this->last_used_at) {
            // Si nunca se ha usado, verificar si fue creado recientemente (últimos 30 min)
            return $this->created_at >= now()->subMinutes(30);
        }

        return $this->last_used_at >= now()->subMinutes(30);
    }

    /**
     * Obtener tiempo desde última actividad
     */
    public function getLastActivityHumanAttribute(): string
    {
        $lastActivity = $this->last_used_at ?? $this->created_at;
        $diff = now()->diffInSeconds($lastActivity);

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
     * Scope: Tokens activos (usados en últimos 30 minutos)
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->where('last_used_at', '>=', now()->subMinutes(30))
              ->orWhere(function($q2) {
                  $q2->whereNull('last_used_at')
                     ->where('created_at', '>=', now()->subMinutes(30));
              });
        });
    }

    /**
     * Scope: Por usuario
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('tokenable_id', $userId)
                     ->where('tokenable_type', User::class);
    }

    /**
     * Obtener user_agent desde abilities
     */
    public function getUserAgentAttribute(): ?string
    {
        $abilities = $this->abilities;
        return is_array($abilities) && isset($abilities['user_agent'])
            ? $abilities['user_agent']
            : null;
    }
}
