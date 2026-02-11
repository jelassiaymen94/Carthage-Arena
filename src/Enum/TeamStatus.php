<?php

namespace App\Enum;

enum TeamStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DISBANDED = 'disbanded';
}
