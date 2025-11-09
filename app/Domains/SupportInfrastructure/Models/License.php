<?php

#namespace App\Domains\SupportInfrastructure\Models;
namespace IncadevUns\CoreDomain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class License extends Model{

    use HasFactory;
    protected $table = 'licenses';

    protected $fillable = ['software_id',
        'key_code',
        'provider',
        'purchase_date',
        'expiration_date',
        'cost',
        'status',
    ];
    
    protected $casts = [
        'purchase_date' => 'datetime',
        'expiration_date' => 'datetime',
        'cost' => 'decimal:2',
    ];

    #probablemente necesite refactorizarse
    public function software(): BelongsTo{
        return $this->belongsTo(Software::class);
    }
}