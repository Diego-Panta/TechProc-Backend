<?php

#namespace IncadevUns\CoreDomain\Models;
namespace App\Domains\SupportInfrastructure\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'user_id'
    ];

    protected $casts = [
        'acquisition_date' => 'datetime',
        'expiration_date' => 'datetime',
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'));
    }

    public function softwares(): HasMany{
        return $this->hasMany(Software::class, 'asset_id');
    }

    public function hardwares(): HasMany {
        return $this->hasMany(Hardware::class, 'asset_id');
    }
}