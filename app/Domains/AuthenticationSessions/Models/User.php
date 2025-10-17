<?php

namespace App\Domains\AuthenticationSessions\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'users';
    public $timestamps = true;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'phone_number',
        'role', 'status', 'last_access_ip', 'last_access', 'last_connection'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_access' => 'datetime', 
        'last_connection' => 'datetime',
        'role' => 'array',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     * VERSIÓN CORREGIDA - Devuelve claims válidos
     */
    public function getJWTCustomClaims()
    {
        return [
            'user_id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'role' => $this->role
        ];
    }

    /**
     * Accessor para role
     */
    public function getRoleAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : ['student'];
        }
        return is_array($value) ? $value : ['student'];
    }

    /**
     * Mutator para role
     */
    public function setRoleAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['role'] = json_encode([$value]);
        } elseif (is_array($value)) {
            $this->attributes['role'] = json_encode($value);
        } else {
            $this->attributes['role'] = json_encode(['student']);
        }
    }

    public function hasRole($role)
    {
        return in_array($role, $this->role);
    }

    public function activeSessions()
    {
        return $this->hasMany(ActiveSession::class, 'user_id');
    }

    public function isActive()
    {
        return $this->status === 'active';
    }
}