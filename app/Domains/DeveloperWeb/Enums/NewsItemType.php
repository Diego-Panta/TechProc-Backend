<?php
namespace App\Domains\DeveloperWeb\Enums;

enum NewsItemType: string
{
    case ARTICLE = 'article';
    case PRESS_RELEASE = 'press-release';
    case UPDATE = 'update';
    case FEATURE = 'feature';
    
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
            self::ARTICLE => 'Artículo',
            self::PRESS_RELEASE => 'Comunicado de Prensa',
            self::UPDATE => 'Actualización',
            self::FEATURE => 'Característica',
        };
    }
}