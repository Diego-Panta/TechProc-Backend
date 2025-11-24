<?php
namespace App\Domains\DeveloperWeb\Enums;

enum NewsCategory: string
{
    case ACADEMIC = 'academic';
    case EVENTS = 'events';
    case COURSES = 'courses';
    case RESEARCH = 'research';
    case ADMINISTRATIVE = 'administrative';
    
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
            self::ACADEMIC => 'Académico',
            self::EVENTS => 'Eventos',
            self::COURSES => 'Cursos y Programas',
            self::RESEARCH => 'Investigación',
            self::ADMINISTRATIVE => 'Administrativo',
        };
    }
    
    /**
     * Obtener categorías con sus etiquetas para selects
     */
    public static function forSelect(): array
    {
        $categories = [];
        foreach (self::cases() as $category) {
            $categories[$category->value] = $category->label();
        }
        return $categories;
    }
}