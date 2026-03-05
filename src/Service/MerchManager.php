<?php

namespace App\Service;

use App\Entity\Merch;
use InvalidArgumentException;

class MerchManager
{
    public function validate(Merch $merch): bool
    {
        if ($merch->getPrice() === null || $merch->getPrice() <= 0) {
            throw new InvalidArgumentException("Le prix d'un produit doit toujours être supérieur à zéro.");
        }

        if ($merch->getStock() === null || $merch->getStock() < 0) {
            throw new InvalidArgumentException("La quantité en stock ne peut pas être négative.");
        }

        return true;
    }
}
