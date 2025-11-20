<?php

namespace App\Domains\AuthenticationSessions\Models;

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

        // Campos de recovery email
        'recovery_email',
        'recovery_email_verified_at',
        'recovery_verification_code',
        'recovery_code_expires_at',

        // Campos de 2FA
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'recovery_verification_code',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'recovery_email_verified_at' => 'datetime',
        'recovery_code_expires_at' => 'datetime',
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
     * Ruta para el email de recuperación en notificaciones
     */
    public function routeNotificationForMail($notification)
    {
        // Si es la notificación de recovery email, enviar al recovery_email
        if ($notification instanceof \App\Domains\AuthenticationSessions\Notifications\VerifyRecoveryEmailNotification) {
            return $this->recovery_email;
        }

        // Para otras notificaciones, usar el email principal
        return $this->email;
    }
}