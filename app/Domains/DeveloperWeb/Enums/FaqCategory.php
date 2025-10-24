<?php
// app/Domains/DeveloperWeb/Enums/FaqCategory.php

namespace App\Domains\DeveloperWeb\Enums;

enum FaqCategory: string
{
    case COURSES = 'cursos';
    case PAYMENTS = 'pagos';
    case CERTIFICATIONS = 'certificaciones';
    case GENERAL = 'general';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::COURSES->value => 'Cursos',
            self::PAYMENTS->value => 'Pagos',
            self::CERTIFICATIONS->value => 'Certificaciones',
            self::GENERAL->value => 'General',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    public static function isValid(string $category): bool
    {
        return in_array($category, self::values());
    }

    public static function getDefault(): self
    {
        return self::GENERAL;
    }
}
