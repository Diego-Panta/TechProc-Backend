<?php

namespace App\Domains\SupportInfrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    use HasFactory;

    protected $table = 'softwares';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_software',
        'software_name',
        'version',
        'category',
        'vendor',
        'license_id',
        'installation_date',
        'last_update',
    ];

    protected $casts = [
        'installation_date' => 'datetime',
        'last_update' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function license()
    {
        return $this->belongsTo(License::class, 'license_id');
    }
}