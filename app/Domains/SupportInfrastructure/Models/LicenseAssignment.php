<?php

namespace App\Domains\SupportInfrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseAssignment extends Model{

    use HasFactory;

    protected $table = 'license_assignments'

    protected $fillable = [
        'license_id',
        'asset_id',
        'assigned_date',
        'status'
    ];
    
    protected $casts = [
        'assigned_date' => 'datetime'
    ];

    #probablemente necesite refactorizarse
    public function license(): BelongsTo{
        return $this->belongsTo(License::class, 'license_id');
    }

    public function asset(): BelongsTo{
        return $this->belongsTo(TechAsset::class, 'asset_id');
    }
}