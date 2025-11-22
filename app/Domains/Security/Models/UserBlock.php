<?php

namespace App\Domains\Security\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBlock extends Model
{
    protected $table = 'user_blocks';

    protected $fillable = [
        'user_id',
        'blocked_by',
        'reason',
        'block_type',
        'ip_address',
        'blocked_at',
        'blocked_until',
        'is_active',
        'unblocked_at',
        'unblocked_by',
        'metadata',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
        'blocked_until' => 'datetime',
        'unblocked_at' => 'datetime',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Relación con el usuario bloqueado
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el usuario que bloqueó
     */
    public function blockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Relación con el usuario que desbloqueó
     */
    public function unblockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unblocked_by');
    }

    /**
     * Verificar si el bloqueo está actualmente vigente
     */
    public function getIsCurrentlyBlockedAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Si no tiene fecha de fin, es permanente
        if (is_null($this->blocked_until)) {
            return true;
        }

        return now()->lt($this->blocked_until);
    }

    /**
     * Obtener tiempo restante de bloqueo en formato legible
     */
    public function getRemainingTimeAttribute(): ?string
    {
        if (!$this->is_currently_blocked) {
            return null;
        }

        if (is_null($this->blocked_until)) {
            return 'Permanente';
        }

        $diff = now()->diff($this->blocked_until);

        if ($diff->d > 0) {
            return $diff->d . ' día' . ($diff->d > 1 ? 's' : '') . ' ' . $diff->h . ' hora' . ($diff->h > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' hora' . ($diff->h > 1 ? 's' : '') . ' ' . $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '');
        } else {
            return $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '');
        }
    }

    /**
     * Obtener el label del tipo de bloqueo
     */
    public function getBlockTypeLabelAttribute(): string
    {
        return match ($this->block_type) {
            'automatic' => 'Automático',
            'manual' => 'Manual',
            default => 'Desconocido',
        };
    }

    /**
     * Scope: Bloqueos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Bloqueos vigentes (activos y no expirados)
     */
    public function scopeCurrentlyBlocked($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('blocked_until')
                    ->orWhere('blocked_until', '>', now());
            });
    }

    /**
     * Scope: Por usuario
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Bloqueos automáticos
     */
    public function scopeAutomatic($query)
    {
        return $query->where('block_type', 'automatic');
    }

    /**
     * Scope: Bloqueos manuales
     */
    public function scopeManual($query)
    {
        return $query->where('block_type', 'manual');
    }

    /**
     * Scope: Bloqueos expirados
     */
    public function scopeExpired($query)
    {
        return $query->where('is_active', true)
            ->whereNotNull('blocked_until')
            ->where('blocked_until', '<=', now());
    }
}
