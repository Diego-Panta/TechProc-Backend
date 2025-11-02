<?php

namespace App\Domains\AuthenticationSessions\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, HasRoles;

    protected $table = 'users';
    public $timestamps = true;

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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relación con sesiones activas
     */
    public function activeSessions()
    {
        return $this->hasMany(ActiveSession::class, 'user_id');
    }
}