<?php

namespace App\Domains\DeveloperWeb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_news',
        'title',
        'slug',
        'summary',
        'content',
        'featured_image',
        'author_id',
        'category',
        'tags',
        'status',
        'views',
        'published_date',
        'created_date',
        'updated_date',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'tags' => 'array',
        'published_date' => 'datetime',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'views' => 'integer',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}