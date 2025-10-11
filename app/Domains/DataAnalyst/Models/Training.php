<?php

namespace App\Domains\DataAnalyst\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $table = 'trainings';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'description',
        'institution',
        'provider',
        'duration_hours',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'duration_hours' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}