<?php

namespace App\Enum;

enum GameStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case COMING_SOON = 'coming_soon';
}
