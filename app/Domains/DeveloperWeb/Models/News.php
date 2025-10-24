<?php

namespace App\Domains\DeveloperWeb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Administrator\Models\User;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';
    protected $primaryKey = 'id';

    // Deshabilitar timestamps automáticos de Laravel
    public $timestamps = false;

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
        'tags' => 'array', // Esto automáticamente convierte JSON a array
        'published_date' => 'datetime',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'views' => 'integer',
    ];

    // Especificar la columna de fecha de creación personalizada
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Accesor para asegurar que tags siempre sea un array
     */
    public function getTagsAttribute($value)
    {
        if (is_string($value)) {
            try {
                return json_decode($value, true) ?? [];
            } catch (\Exception $e) {
                return [];
            }
        }
        
        return $value ?? [];
    }

    /**
     * Mutador para convertir array a JSON al guardar
     */
    public function setTagsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['tags'] = json_encode($value);
        } else {
            $this->attributes['tags'] = $value;
        }
    }
}