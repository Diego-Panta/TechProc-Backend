<?php
// app/Domains/DeveloperWeb/Enums/ContactFormStatus.php

namespace App\Domains\DeveloperWeb\Enums;

enum ContactFormStatus: string
{
    case PENDING = 'pending';
    case RESPONDED = 'responded';
    case SPAM = 'spam';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::PENDING->value => 'Pendiente',
            self::RESPONDED->value => 'Respondido',
            self::SPAM->value => 'Spam',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    public static function isValid(string $status): bool
    {
        return in_array($status, self::values());
    }

    public static function getDefault(): self
    {
        return self::PENDING;
    }

    /**
     * Obtener estados que requieren acción (no finalizados)
     */
    public static function getActiveStatuses(): array
    {
        return [
            self::PENDING->value,
        ];
    }

    /**
     * Obtener estados finalizados
     */
    public static function getFinalStatuses(): array
    {
        return [
            self::RESPONDED->value,
            self::SPAM->value,
        ];
    }

    /**
     * Verificar si el estado es final
     */
    public function isFinal(): bool
    {
        return in_array($this->value, self::getFinalStatuses());
    }

    /**
     * Safe from method para evitar errores con valores inválidos
     */
    public static function safeFrom(?string $value): ?self
    {
        if ($value === null || !self::isValid($value)) {
            return null;
        }
        
        return self::from($value);
    }

}