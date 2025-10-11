<?php

namespace App\Domains\DeveloperWeb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $table = 'alerts';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_alert',
        'message',
        'type',
        'status',
        'link_url',
        'link_text',
        'start_date',
        'end_date',
        'priority',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_date' => 'datetime',
        'priority' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}