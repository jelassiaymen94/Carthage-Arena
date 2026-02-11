<?php

namespace App\Enum;

enum TeamRole: string
{
    case CAPTAIN = 'captain';
    case CO_CAPTAIN = 'co_captain';
    case MEMBER = 'member';
}
