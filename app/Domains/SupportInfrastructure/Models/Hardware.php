<?php

namespace App\Domains\SupportInfrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Databas\Eloquent\Relations\BelongsTo;

class Hardware extends Model
{
    use HasFactory;

    protected $table = 'hardwares';

    protected $fillable = [
        'asset_id',
        'model',
        'serial_number',
        'warranty_expiration',
        'specs'
    ];

    public function asset(): BelongsTo{
        return $this->belongsTo(TechAsset::class, 'asset_id');
    }

}