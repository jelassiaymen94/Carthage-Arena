<?php

namespace App\Enum;

enum ReclamationCategory: string
{
    case TECHNICAL = 'technical';
    case TOURNAMENT = 'tournament';
    case PAYMENT = 'payment';
    case ACCOUNT = 'account';
    case OTHER = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::TECHNICAL => 'Problème Technique',
            self::TOURNAMENT => 'Tournoi & Matchs',
            self::PAYMENT => 'Paiement & Boutique',
            self::ACCOUNT => 'Compte & Sécurité',
            self::OTHER => 'Autre',
        };
    }

    public function getColor(): string
    {
        return 'bg-white/10 text-white border border-white/20';
    }
}
