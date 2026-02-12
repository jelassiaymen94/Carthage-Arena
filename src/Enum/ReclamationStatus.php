<?php

namespace App\Enum;

enum ReclamationStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'En Attente',
            self::IN_PROGRESS => 'En Cours',
            self::RESOLVED => 'Résolu',
            self::CLOSED => 'Fermé',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'bg-gray-500/20 text-gray-400 border-gray-500/30',
            self::IN_PROGRESS => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
            self::RESOLVED => 'bg-green-500/20 text-green-400 border-green-500/30',
            self::CLOSED => 'bg-red-500/20 text-red-400 border-red-500/30',
        };
    }
}
