<?php

namespace App\Service;

use App\Entity\MatchEntity;

class MatchManager
{
    /**
     * Valide les règles métier d'un match.
     * 
     * @throws \InvalidArgumentException si une règle n'est pas respectée.
     */
    public function validate(MatchEntity $match): bool
    {
        // Règle 1 : Les deux équipes d'un match doivent être différentes
        if ($match->getTeam1() !== null && $match->getTeam2() !== null && $match->getTeam1() === $match->getTeam2()) {
            throw new \InvalidArgumentException("Les deux équipes d'un match doivent être différentes");
        }

        // Règle 2 : Le score ne peut pas être négatif
        $score = $match->getScore();
        if ($score !== null) {
            foreach ($score as $points) {
                if ($points < 0) {
                    throw new \InvalidArgumentException('Le score ne peut pas être négatif');
                }
            }
        }

        // Règle 3 : Le round doit être supérieur à 0
        if ($match->getRound() <= 0) {
            throw new \InvalidArgumentException('Le round doit être supérieur à 0');
        }

        // Règle 4 : Si le statut est COMPLETED, le winner doit être défini
        if ($match->getStatus() === \App\Enum\MatchStatus::COMPLETED && $match->getWinner() === null) {
            throw new \InvalidArgumentException('Un match terminé doit avoir un gagnant');
        }

        // Règle 5 : Les deux équipes doivent être présentes si le statut est ONGOING
        if ($match->getStatus() === \App\Enum\MatchStatus::IN_PROGRESS) {
            if ($match->getTeam1() === null || $match->getTeam2() === null) {
                throw new \InvalidArgumentException('Les deux équipes doivent être présentes pour démarrer le match');
            }
        }

        return true;
    }
}
