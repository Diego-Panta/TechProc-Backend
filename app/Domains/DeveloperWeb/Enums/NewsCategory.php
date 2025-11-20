<?php
namespace App\Domains\DeveloperWeb\Enums;

enum NewsCategory: string
{
    case TECHNOLOGY = 'technology';
    case SCIENCE = 'science';
    case BUSINESS = 'business';
    case HEALTH = 'health';
    case SPORTS = 'sports';
    case ENTERTAINMENT = 'entertainment';
    case POLITICS = 'politics';
    case EDUCATION = 'education';
    case TRAVEL = 'travel';
    case LIFESTYLE = 'lifestyle';
    
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
            self::TECHNOLOGY => 'Tecnología',
            self::SCIENCE => 'Ciencia',
            self::BUSINESS => 'Negocios',
            self::HEALTH => 'Salud',
            self::SPORTS => 'Deportes',
            self::ENTERTAINMENT => 'Entretenimiento',
            self::POLITICS => 'Política',
            self::EDUCATION => 'Educación',
            self::TRAVEL => 'Viajes',
            self::LIFESTYLE => 'Estilo de Vida',
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