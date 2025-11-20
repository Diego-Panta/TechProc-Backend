<?php
namespace App\Domains\DeveloperWeb\Enums;

enum ContentType: string
{
    case NEWS = 'news';
    case ANNOUNCEMENT = 'announcement';
    case ALERT = 'alert';
    case EVENT = 'event';
}