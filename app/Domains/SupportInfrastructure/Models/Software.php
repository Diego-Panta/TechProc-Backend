<?php

namespace App\Domains\SupportInfrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Software extends Model
{
    use HasFactory;

    protected $table = 'softwares';

    protected $fillable = [
        'software_name',
        'version',
        'type',
    ];

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class, 'software_id');
    }
}