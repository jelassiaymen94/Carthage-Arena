<?php

namespace App\Service;

use App\Entity\Tournoi;

class TournoiManager
{
    /**
     * Valide les règles métier d'un tournoi.
     * 
     * @throws \InvalidArgumentException si une règle n'est pas respectée.
     */
    public function validate(Tournoi $tournoi): bool
    {
        // Règle 1 : Le nom du tournoi est obligatoire
        if (empty($tournoi->getNom())) {
            throw new \InvalidArgumentException('Le nom du tournoi est obligatoire');
        }

        // Règle 2 : La date de fin doit être postérieure à la date de début
        if ($tournoi->getDateFin() <= $tournoi->getDateDebut()) {
            throw new \InvalidArgumentException('La date de fin doit être postérieure à la date de début');
        }

        // Règle 3 : Le nombre d'équipes max doit être au moins 2
        if ($tournoi->getNbEquipesMax() < 2) {
            throw new \InvalidArgumentException("Le nombre d'équipes max doit être au moins 2");
        }

        // Règle 4 : Le prize pool ne peut pas être négatif
        if ($tournoi->getPrizePool() < 0) {
            throw new \InvalidArgumentException('Le prize pool ne peut pas être négatif');
        }

        // Règle 5 : La date de début ne peut pas être dans le passé
        if ($tournoi->getDateDebut() < new \DateTimeImmutable('today')) {
            throw new \InvalidArgumentException('La date de début ne peut pas être dans le passé');
        }

        return true;
    }
}
