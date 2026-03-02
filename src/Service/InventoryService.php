<?php

namespace App\Service;

use App\Entity\Skin;
use App\Enum\SkinType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class InventoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private HttpClientInterface $httpClient,
    ) {}

    public function checkStock(Skin $skin): bool
    {
        if ($skin->getType() === SkinType::PHYSICAL) {
            return $skin->getStock() > 0;
        }

        // Pour digital, v├®rifier via API tierce
        return $this->checkDigitalStock($skin);
    }

    private function checkDigitalStock(Skin $skin): bool
    {
        if ($skin->getApiProvider() === 'skinport') {
            return true; // Placeholder
        }

        return true;
    }

    public function reserveStock(Skin $skin, int $quantity = 1): bool
    {
        if ($skin->getType() === SkinType::PHYSICAL) {
            if ($skin->getStock() >= $quantity) {
                $skin->setStock($skin->getStock() - $quantity);
                $this->entityManager->flush();
                return true;
            }
        } else {
            return $this->reserveDigitalStock($skin);
        }

        return false;
    }

    private function reserveDigitalStock(Skin $skin): bool
    {
        return true;
    }
}
