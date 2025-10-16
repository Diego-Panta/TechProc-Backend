<?php

namespace App\Domains\DeveloperWeb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Administrator\Models\User;

class Alert extends Model
{
    use HasFactory;

    protected $table = 'alerts';
    protected $primaryKey = 'id';

    // Deshabilitar timestamps automáticos de Laravel
    public $timestamps = false;

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
        'created_date', // Agregado
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_date' => 'datetime',
        'priority' => 'integer',
    ];

    // Especificar la columna de fecha de creación personalizada
    const CREATED_AT = 'created_date';
    const UPDATED_AT = null; // No hay columna updated_at

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}