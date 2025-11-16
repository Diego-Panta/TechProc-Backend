<?php

namespace App\Domains\Security\Enums;

enum SecurityEventSeverity: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case CRITICAL = 'critical';

    /**
     * Get all values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get label for display
     */
    public function label(): string
    {
        return match($this) {
            self::INFO => 'Informativo',
            self::WARNING => 'Advertencia',
            self::CRITICAL => 'Crítico',
        };
    }

    /**
     * Get color for display
     */
    public function color(): string
    {
        return match($this) {
            self::INFO => 'blue',
            self::WARNING => 'yellow',
            self::CRITICAL => 'red',
        };
    }
}
