<?php
// app/Domains/DeveloperWeb/Services/ChatbotConfigService.php

namespace App\Domains\DeveloperWeb\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChatbotConfigService
{
    private $cacheKey = 'chatbot_config';
    private $cacheDuration = 86400; // 24 horas en segundos

    public function getConfig(): array
    {
        return Cache::remember($this->cacheKey, $this->cacheDuration, function () {
            return $this->getDefaultConfig();
        });
    }

    public function updateConfig(array $config): bool
    {
        try {
            $currentConfig = $this->getConfig();
            $updatedConfig = array_merge($currentConfig, $config);
            
            // Validar los valores
            $validatedConfig = $this->validateConfig($updatedConfig);
            
            // Guardar en cache
            Cache::put($this->cacheKey, $validatedConfig, $this->cacheDuration);
            
            // También guardar en cache persistente (opcional)
            $this->saveToPersistentCache($validatedConfig);
            
            Log::info('Chatbot config updated', ['config' => $validatedConfig]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error updating chatbot config: ' . $e->getMessage());
            return false;
        }
    }

    private function getDefaultConfig(): array
    {
        return [
            'enabled' => true,
            'greeting_message' => '¡Hola! Soy el asistente virtual. ¿En qué puedo ayudarte hoy?',
            'fallback_message' => 'Lo siento, no entendí tu pregunta. ¿Podrías reformularla?',
            'response_delay' => 1000,
            'max_conversations_per_day' => 1000,
            'contact_threshold' => 3,
            'updated_at' => now()->toISOString()
        ];
    }

    private function validateConfig(array $config): array
    {
        return [
            'enabled' => (bool)($config['enabled'] ?? true),
            'greeting_message' => $this->validateString($config['greeting_message'] ?? '', '¡Hola! Soy el asistente virtual. ¿En qué puedo ayudarte hoy?'),
            'fallback_message' => $this->validateString($config['fallback_message'] ?? '', 'Lo siento, no entendí tu pregunta. ¿Podrías reformularla?'),
            'response_delay' => $this->validateInteger($config['response_delay'] ?? 1000, 0, 10000),
            'max_conversations_per_day' => $this->validateInteger($config['max_conversations_per_day'] ?? 1000, 0, 100000),
            'contact_threshold' => $this->validateInteger($config['contact_threshold'] ?? 3, 1, 10),
            'updated_at' => now()->toISOString()
        ];
    }

    private function validateString(string $value, string $default): string
    {
        $trimmed = trim($value);
        return empty($trimmed) ? $default : $trimmed;
    }

    private function validateInteger($value, int $min, int $max): int
    {
        $intValue = (int)$value;
        return max($min, min($max, $intValue));
    }

    /**
     * Guardar en cache persistente (usando el driver de cache por defecto)
     * Esto sobrevive a reinicios de PHP pero no a limpiezas de cache
     */
    private function saveToPersistentCache(array $config): void
    {
        // El cache principal ya se guarda, este es un backup
        $backupKey = $this->cacheKey . '_backup';
        Cache::put($backupKey, $config, $this->cacheDuration * 2); // 48 horas
    }

    /**
     * Restaurar configuración desde backup si es necesario
     */
    public function restoreFromBackup(): bool
    {
        try {
            $backupKey = $this->cacheKey . '_backup';
            $backupConfig = Cache::get($backupKey);
            
            if ($backupConfig) {
                Cache::put($this->cacheKey, $backupConfig, $this->cacheDuration);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Error restoring chatbot config from backup: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Resetear a configuración por defecto
     */
    public function resetToDefault(): bool
    {
        try {
            $defaultConfig = $this->getDefaultConfig();
            Cache::put($this->cacheKey, $defaultConfig, $this->cacheDuration);
            return true;
        } catch (\Exception $e) {
            Log::error('Error resetting chatbot config: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar estado del servicio
     */
    public function getHealthStatus(): array
    {
        try {
            $config = $this->getConfig();
            $hasConfig = !empty($config);
            
            return [
                'status' => 'healthy',
                'config_loaded' => $hasConfig,
                'cache_driver' => config('cache.default'),
                'config_keys' => array_keys($config),
                'cache_key' => $this->cacheKey
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
    }
}