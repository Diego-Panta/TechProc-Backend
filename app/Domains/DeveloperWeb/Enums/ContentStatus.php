<?php
namespace App\Domains\DeveloperWeb\Enums;

enum ContentStatus: string
{
    // Estados para NEWS
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case SCHEDULED = 'scheduled';
    
    // Estados para ANNOUNCEMENT y ALERT
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    
    /**
     * Obtener estados válidos para NEWS
     */
    public static function forNews(): array
    {
        return [
            self::DRAFT->value,
            self::PUBLISHED->value,
            self::ARCHIVED->value,
            self::SCHEDULED->value,
        ];
    }
    
    /**
     * Obtener estados válidos para ANNOUNCEMENT
     */
    public static function forAnnouncement(): array
    {
        return [
            self::ACTIVE->value,
            self::INACTIVE->value,
        ];
    }
    
    /**
     * Obtener estados válidos para ALERT
     */
    public static function forAlert(): array
    {
        return [
            self::ACTIVE->value,
            self::INACTIVE->value,
        ];
    }
}