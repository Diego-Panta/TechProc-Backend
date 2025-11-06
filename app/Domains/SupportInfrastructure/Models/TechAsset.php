<?php

namespace App\Domains\SupportInfrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechAsset extends Model
{
    use HasFactory;

    protected $table = 'tech_assets';

    protected $fillable = [
        'name',
        'type',
        'status',
        'acquisition_date',
        'expiration_date',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'expiration_date' => 'date',
    ];


}