<?php

namespace App\Service;

use App\Entity\Reclamation;
use App\Enum\ReclamationStatus;

class ReclamationManager
{
    /**
     * Valide les règles métier d'une réclamation.
     * 
     * @throws \InvalidArgumentException si une règle n'est pas respectée.
     */
    public function validate(Reclamation $reclamation): bool
    {
        // Règle 1 : Le sujet est obligatoire
        if (empty($reclamation->getSubject())) {
            throw new \InvalidArgumentException('Le sujet est obligatoire');
        }

        // Règle 2 : Le message doit faire au moins 15 caractères
        if (strlen($reclamation->getMessage() ?? '') < 15) {
            throw new \InvalidArgumentException('Le message doit faire au moins 15 caractères');
        }

        // Règle 3 : L'auteur est obligatoire
        if ($reclamation->getAuthor() === null) {
            throw new \InvalidArgumentException("L'auteur est obligatoire");
        }

        // Règle 4 : Le statut est obligatoire
        if ($reclamation->getStatus() === null) {
            throw new \InvalidArgumentException('Le statut est obligatoire');
        }

        // Règle 5 : Une réclamation résolue ne peut plus être modifiée
        // Note: Dans un environnement réel, on vérifierait l'état précédent en base.
        // Ici, on valide que si on tente de valider une réclamation déjà RESOLVED, c'est bloqué.
        if ($reclamation->getStatus() === ReclamationStatus::RESOLVED) {
            throw new \InvalidArgumentException('Une réclamation résolue ne peut plus être modifiée');
        }

        return true;
    }
}
