<?php

namespace App\Domains\DeveloperWeb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $table = 'announcements';
    protected $primaryKey = 'id';

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
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_date' => 'datetime',
        'views' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}