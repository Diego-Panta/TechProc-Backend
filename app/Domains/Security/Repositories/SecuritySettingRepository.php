<?php

namespace App\Domains\Security\Repositories;

use App\Domains\Security\Models\SecuritySetting;
use Illuminate\Support\Collection;

class SecuritySettingRepository
{
    /**
     * Obtener todas las configuraciones
     */
    public function all(): Collection
    {
        return SecuritySetting::orderBy('group')->orderBy('key')->get();
    }

    /**
     * Obtener configuraciones por grupo
     */
    public function getByGroup(string $group): Collection
    {
        return SecuritySetting::inGroup($group)->get();
    }

    /**
     * Obtener una configuración por key
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return SecuritySetting::get($key, $default);
    }

    /**
     * Establecer una configuración
     */
    public function set(string $key, mixed $value, ?string $type = null, ?string $description = null, ?string $group = null): SecuritySetting
    {
        return SecuritySetting::set($key, $value, $type, $description, $group);
    }

    /**
     * Actualizar múltiples configuraciones
     */
    public function updateMany(array $settings): array
    {
        $updated = [];

        foreach ($settings as $key => $value) {
            $setting = SecuritySetting::where('key', $key)->first();

            if ($setting) {
                $setting->update(['value' => (string) $value]);
                SecuritySetting::clearCache($key);
                $updated[$key] = $setting->refresh()->typed_value;
            }
        }

        return $updated;
    }

    /**
     * Obtener configuración de login
     */
    public function getLoginSettings(): array
    {
        return [
            'max_failed_login_attempts' => $this->get('max_failed_login_attempts', 5),
            'block_duration_minutes' => $this->get('block_duration_minutes', 30),
            'failed_login_window_minutes' => $this->get('failed_login_window_minutes', 10),
        ];
    }

    /**
     * Verificar si una configuración existe
     */
    public function exists(string $key): bool
    {
        return SecuritySetting::where('key', $key)->exists();
    }

    /**
     * Eliminar una configuración
     */
    public function delete(string $key): bool
    {
        SecuritySetting::clearCache($key);
        return SecuritySetting::where('key', $key)->delete() > 0;
    }

    /**
     * Limpiar todo el cache
     */
    public function clearAllCache(): void
    {
        SecuritySetting::clearAllCache();
    }
}
