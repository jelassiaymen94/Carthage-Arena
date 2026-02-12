<?php

namespace App\Enum;

enum GameType: string
{
    case FPS = 'fps';
    case MMORPG = 'mmorpg';
    case MOBA = 'moba';
    case BATTLE_ROYALE = 'battle_royale';
    case STRATEGY = 'strategy';
    case SPORTS = 'sports';
}
