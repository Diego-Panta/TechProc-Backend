<?php

namespace App\Domains\SupportInfrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;

    protected $table = 'licenses';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_license',
        'software_name',
        'license_key',
        'license_type',
        'provider',
        'purchase_date',
        'expiration_date',
        'seats_total',
        'seats_used',
        'cost_annual',
        'status',
        'responsible_id',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expiration_date' => 'date',
        'seats_total' => 'integer',
        'seats_used' => 'integer',
        'cost_annual' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function responsible()
    {
        return $this->belongsTo(Employee::class, 'responsible_id');
    }

    public function softwares()
    {
        return $this->hasMany(Software::class, 'license_id');
    }
}