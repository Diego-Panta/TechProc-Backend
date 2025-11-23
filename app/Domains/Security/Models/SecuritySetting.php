<?php

namespace App\Domains\Security\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SecuritySetting extends Model
{
    protected $table = 'security_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'group',
    ];

    /**
     * Cache key prefix
     */
    private const CACHE_PREFIX = 'security_setting_';
    private const CACHE_TTL = 3600; // 1 hora

    /**
     * Obtener valor tipado según el tipo configurado
     */
    public function getTypedValueAttribute(): mixed
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'float' => (float) $this->value,
            default => $this->value,
        };
    }

    /**
     * Obtener una configuración por key con cache
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->typed_value : $default;
        });
    }

    /**
     * Establecer una configuración (actualiza o crea)
     */
    public static function set(string $key, mixed $value, ?string $type = null, ?string $description = null, ?string $group = null): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            array_filter([
                'value' => (string) $value,
                'type' => $type,
                'description' => $description,
                'group' => $group,
            ], fn ($v) => !is_null($v))
        );

        // Limpiar cache
        Cache::forget(self::CACHE_PREFIX . $key);

        return $setting;
    }

    /**
     * Limpiar cache de una configuración
     */
    public static function clearCache(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
    }

    /**
     * Limpiar todo el cache de configuraciones
     */
    public static function clearAllCache(): void
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget(self::CACHE_PREFIX . $setting->key);
        }
    }

    /**
     * Scope: Por grupo
     */
    public function scopeInGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Obtener todas las configuraciones de un grupo
     */
    public static function getGroup(string $group): array
    {
        return static::inGroup($group)
            ->get()
            ->mapWithKeys(fn ($setting) => [$setting->key => $setting->typed_value])
            ->toArray();
    }
}
