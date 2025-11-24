<?php

namespace App\Domains\DeveloperWeb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Domains\DeveloperWeb\Enums\ContentType;
use App\Domains\DeveloperWeb\Enums\ContentStatus;

class ContentItem extends Model
{
    use HasFactory;

    protected $table = 'content_items';

    protected $fillable = [
        'content_type',
        'title',
        'slug',
        'content',
        'summary',
        'image_url',
        'status',
        'views',
        'priority',
        'start_date',
        'end_date',
        'published_date',
        'category',
        'item_type',
        'target_page',
        'link_url',
        'link_text',
        'button_text',
        'seo_title',
        'seo_description',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'published_date' => 'datetime',
        'metadata' => 'array',
        'views' => 'integer',
        'priority' => 'integer',
        // Opcional: castear a Enums si quieres
        // 'content_type' => ContentType::class,
        // 'status' => ContentStatus::class,
    ];

    /**
     * Scope para filtrar por tipo de contenido
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('content_type', $type);
    }

    /**
     * Scope para noticias
     */
    public function scopeNews($query)
    {
        return $query->ofType(ContentType::NEWS->value);
    }

    /**
     * Scope para anuncios
     */
    public function scopeAnnouncements($query)
    {
        return $query->ofType(ContentType::ANNOUNCEMENT->value);
    }

    /**
     * Scope para alertas
     */
    public function scopeAlerts($query)
    {
        return $query->ofType(ContentType::ALERT->value);
    }

    /**
     * Scope para contenido publicado/activo según tu lógica de negocio
     */
    public function scopePublished($query)
    {
        return $query->where('status', ContentStatus::PUBLISHED->value);
    }

    /**
     * Scope para contenido activo en fechas
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('start_date')
              ->orWhere('start_date', '<=', now());
        })->where(function ($q) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', now());
        });
    }

    /**
     * Scope para contenido que debe mostrarse (publicado + activo en fechas)
     */
    public function scopeShouldBeDisplayed($query)
    {
        return $query->published()->active();
    }

    /**
     * Scope para anuncios activos (valida fechas Y que status sea active o published)
     */
    public function scopeActiveByDate($query)
    {
        return $query->whereIn('status', [
            ContentStatus::ACTIVE->value,
            ContentStatus::PUBLISHED->value
        ])->active();
    }

    /**
     * Accessor para determinar si el contenido está activo
     */
    protected function isActive(): Attribute
    {
        return Attribute::make(
            get: function () {
                $now = now();
                $statusActive = $this->status === ContentStatus::PUBLISHED->value;
                $dateActive = (!$this->start_date || $this->start_date <= $now) && 
                             (!$this->end_date || $this->end_date >= $now);
                
                return $statusActive && $dateActive;
            }
        );
    }

    /**
     * Accessor para la URL basada en el tipo
     */
    protected function publicUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->content_type) {
                    ContentType::NEWS->value => route('news.show', $this->slug),
                    ContentType::ANNOUNCEMENT->value => $this->link_url,
                    ContentType::ALERT->value => $this->link_url,
                    default => null,
                };
            }
        );
    }

    /**
     * Verificar si es de un tipo específico
     */
    public function isNews(): bool
    {
        return $this->content_type === ContentType::NEWS->value;
    }

    public function isAnnouncement(): bool
    {
        return $this->content_type === ContentType::ANNOUNCEMENT->value;
    }

    public function isAlert(): bool
    {
        return $this->content_type === ContentType::ALERT->value;
    }

    /**
     * Incrementar vistas
     */
    public function incrementViews(): void
    {
        $this->increment('views');
    }
}