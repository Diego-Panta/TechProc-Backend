<?php

namespace App\Domains\DataAnalyst\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueSource extends Model
{
    use HasFactory;

    protected $table = 'revenue_sources';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'revenue_source_id');
    }
}