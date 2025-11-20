<?php
namespace App\Domains\DeveloperWeb\Enums;

enum AnnouncementItemType: string
{
    case POPUP = 'popup';
    case BANNER = 'banner';
    case MODAL = 'modal';
    case NOTIFICATION = 'notification';
    
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
            self::POPUP => 'Popup',
            self::BANNER => 'Banner',
            self::MODAL => 'Modal',
            self::NOTIFICATION => 'Notificación',
        };
    }
}