<?php

namespace App\Enum;

enum TournamentType: string
{
    case ELIMINATION = 'elimination';
    case ROUND_ROBIN = 'round_robin';
    case LEAGUE = 'league';
    case SWISS = 'swiss';

    public function label(): string
    {
        return match ($this) {
            self::ELIMINATION => 'Single Elimination',
            self::ROUND_ROBIN => 'Round Robin',
            self::LEAGUE => 'League',
            self::SWISS => 'Swiss System',
        };
    }
}
