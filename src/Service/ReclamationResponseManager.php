<?php

namespace App\Service;

use App\Entity\ReclamationResponse;
use App\Enum\ReclamationStatus;

class ReclamationResponseManager
{
    /**
     * Valide les règles métier d'une réponse à une réclamation.
     * 
     * @throws \InvalidArgumentException si une règle n'est pas respectée.
     */
    public function validate(ReclamationResponse $response): bool
    {
        // Règle 1 : Le message est obligatoire
        if (empty($response->getMessage())) {
            throw new \InvalidArgumentException('Le message de réponse ne peut pas être vide');
        }

        // Règle 2 : Le message doit faire au moins 10 caractères
        if (strlen($response->getMessage() ?? '') < 10) {
            throw new \InvalidArgumentException('Le message de réponse doit faire au moins 10 caractères');
        }

        // Règle 3 : L'auteur est obligatoire
        if ($response->getAuthor() === null) {
            throw new \InvalidArgumentException("L'auteur de la réponse est obligatoire");
        }

        // Règle 4 : La réclamation parente est obligatoire
        if ($response->getReclamation() === null) {
            throw new \InvalidArgumentException('La réclamation parente est obligatoire');
        }

        // Règle 5 : On ne peut pas répondre à une réclamation déjà résolue
        if ($response->getReclamation()->getStatus() === ReclamationStatus::RESOLVED) {
            throw new \InvalidArgumentException('Impossible de répondre à une réclamation déjà résolue');
        }

        return true;
    }
}
