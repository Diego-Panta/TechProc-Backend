<?php

namespace App\Domains\SupportSecurity\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityConfiguration extends Model
{
    use HasFactory;

    protected $table = 'security_configurations';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_security_configuration',
        'user_id',
        'modulo',
        'parameter',
        'value',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}