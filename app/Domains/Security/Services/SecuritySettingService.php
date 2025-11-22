<?php

namespace App\Domains\Security\Services;

use App\Domains\Security\Models\SecuritySetting;
use App\Domains\Security\Repositories\SecuritySettingRepository;
use Illuminate\Support\Collection;

class SecuritySettingService
{
    public function __construct(
        private SecuritySettingRepository $settingRepository
    ) {}

    /**
     * Obtener todas las configuraciones
     */
    public function getAllSettings(): Collection
    {
        return $this->settingRepository->all();
    }

    /**
     * Obtener configuraciones por grupo
     */
    public function getSettingsByGroup(string $group): Collection
    {
        return $this->settingRepository->getByGroup($group);
    }

    /**
     * Obtener una configuración específica
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->settingRepository->get($key, $default);
    }

    /**
     * Obtener configuración de login/bloqueo
     */
    public function getLoginSettings(): array
    {
        return $this->settingRepository->getLoginSettings();
    }

    /**
     * Actualizar una configuración
     */
    public function update(string $key, mixed $value): array
    {
        if (!$this->settingRepository->exists($key)) {
            return [
                'success' => false,
                'message' => 'La configuración no existe',
            ];
        }

        $setting = $this->settingRepository->set($key, $value);

        return [
            'success' => true,
            'message' => 'Configuración actualizada',
            'data' => [
                'key' => $setting->key,
                'value' => $setting->typed_value,
            ],
        ];
    }

    /**
     * Actualizar múltiples configuraciones
     */
    public function updateMany(array $settings): array
    {
        $updated = $this->settingRepository->updateMany($settings);

        return [
            'success' => true,
            'message' => 'Configuraciones actualizadas',
            'data' => $updated,
        ];
    }

    /**
     * Obtener max intentos fallidos de login
     */
    public function getMaxFailedLoginAttempts(): int
    {
        return (int) $this->get('max_failed_login_attempts', 5);
    }

    /**
     * Obtener duración de bloqueo en minutos
     */
    public function getBlockDurationMinutes(): int
    {
        return (int) $this->get('block_duration_minutes', 30);
    }

    /**
     * Obtener ventana de tiempo para intentos fallidos (minutos)
     */
    public function getFailedLoginWindowMinutes(): int
    {
        return (int) $this->get('failed_login_window_minutes', 10);
    }

    /**
     * Limpiar cache de configuraciones
     */
    public function clearCache(): void
    {
        $this->settingRepository->clearAllCache();
    }

    /**
     * Obtener todas las configuraciones agrupadas para API
     */
    public function getAllSettingsGrouped(): array
    {
        $settings = $this->getAllSettings();

        return $settings->groupBy('group')
            ->map(fn ($items) => $items->mapWithKeys(
                fn ($item) => [$item->key => [
                    'value' => $item->typed_value,
                    'type' => $item->type,
                    'description' => $item->description,
                ]]
            ))
            ->toArray();
    }
}
