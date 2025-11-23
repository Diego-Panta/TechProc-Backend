<?php

namespace App\Models;

use App\Domains\AuthenticationSessions\Models\ActiveSession;
use App\Domains\AuthenticationSessions\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, HasRoles, Notifiable;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    protected $table = 'users';
    public $timestamps = true;
    protected $guard_name = 'web';

    protected $fillable = [
        // Campos básicos de Laravel
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',

        // Campos del vendor (incadev/core)
        'dni',
        'fullname',
        'avatar',
        'phone',

        // Campos de email secundario (notificaciones, recuperación, etc.)
        'secondary_email',
        'secondary_email_verified_at',
        'secondary_email_verification_code',
        'secondary_email_code_expires_at',

        // Campos de 2FA
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'secondary_email_verification_code',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'secondary_email_verified_at' => 'datetime',
        'secondary_email_code_expires_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_enabled' => 'boolean',
    ];

    /**
     * Relación con sesiones activas
     */
    public function activeSessions()
    {
        return $this->hasMany(ActiveSession::class, 'user_id');
    }

    /**
     * Enviar notificación de reseteo de contraseña
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Ruta para el email en notificaciones
     *
     * Usa el secondary_email verificado para TODAS las notificaciones si está disponible.
     * De lo contrario, usa el email principal.
     */
    public function routeNotificationForMail($notification)
    {
        // Si es la notificación de verificación de secondary email, enviar al secondary_email
        if ($notification instanceof \App\Domains\AuthenticationSessions\Notifications\VerifySecondaryEmailNotification) {
            return $this->secondary_email;
        }

        // Para TODAS las demás notificaciones:
        // Si tiene secondary_email verificado, usar ese; de lo contrario, usar email principal
        if ($this->secondary_email && $this->secondary_email_verified_at) {
            return $this->secondary_email;
        }

        // Si no hay secondary_email verificado, usar el email principal
        return $this->email;
    }
}
