<?php
namespace App\Domains\DeveloperWeb\Enums;

enum AlertItemType: string
{
    case INFORMATION = 'information';
    case WARNING = 'warning';
    case SUCCESS = 'success';
    case ERROR = 'error';
    case MAINTENANCE = 'maintenance';
    
    /**
     * Obtener todos los tipos como array
     */
    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
    
    /**
     * Obtener etiquetas para mostrar
     */
    public function label(): string
    {
        return match($this) {
            self::INFORMATION => 'Información',
            self::WARNING => 'Advertencia',
            self::SUCCESS => 'Éxito',
            self::ERROR => 'Error',
            self::MAINTENANCE => 'Mantenimiento',
        };
    }
    
    /**
     * Obtener clase CSS para el tipo de alerta
     */
    public function cssClass(): string
    {
        return match($this) {
            self::INFORMATION => 'alert-info',
            self::WARNING => 'alert-warning',
            self::SUCCESS => 'alert-success',
            self::ERROR => 'alert-error',
            self::MAINTENANCE => 'alert-maintenance',
        };
    }
    
    /**
     * Obtener ícono para el tipo de alerta
     */
    public function icon(): string
    {
        return match($this) {
            self::INFORMATION => 'ℹ️',
            self::WARNING => '⚠️',
            self::SUCCESS => '✅',
            self::ERROR => '❌',
            self::MAINTENANCE => '🔧',
        };
    }
}