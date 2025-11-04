<?php

namespace App\Domains\DeveloperWeb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\AuthenticationSessions\Models\User;

class Announcement extends Model
{
    use HasFactory;

    protected $table = 'announcements';
    protected $primaryKey = 'id';

    // Deshabilitar timestamps automáticos de Laravel
    public $timestamps = false;

    protected $fillable = [
        'id_announcement',
        'title',
        'content',
        'image_url',
        'display_type',
        'target_page',
        'link_url',
        'button_text',
        'status',
        'start_date',
        'end_date',
        'views',
        'created_by',
        'created_date',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_date' => 'datetime',
        'views' => 'integer',
    ];

    // Especificar la columna de fecha de creación personalizada
    const CREATED_AT = 'created_date';
    const UPDATED_AT = null; // No hay columna updated_at

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}