<?php
// app/Domains/DeveloperWeb/Enums/FaqCategory.php

namespace App\Domains\DeveloperWeb\Enums;

enum FaqCategory: string
{
    case GENERAL = 'general';
    case ACADEMICO = 'academico';
    case TECNICO = 'tecnico';
    case PAGOS = 'pagos';
    case SOPORTE = 'soporte';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::GENERAL->value   => 'General',
            self::ACADEMICO->value => 'Académico',
            self::TECNICO->value   => 'Técnico',
            self::PAGOS->value     => 'Pagos',
            self::SOPORTE->value   => 'Soporte',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    public static function isValid(string $category): bool
    {
        return in_array($category, self::values(), true);
    }

    public static function getDefault(): self
    {
        return self::GENERAL;
    }
}
