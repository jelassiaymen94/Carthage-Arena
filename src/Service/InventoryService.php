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

        // Pour digital, vérifier via API tierce
        return $this->checkDigitalStock($skin);
    }

    private function checkDigitalStock(Skin $skin): bool
    {
        // Exemple pour Steam API via Skinport
        if ($skin->getApiProvider() === 'skinport') {
            // Simuler un appel API
            // $response = $this->httpClient->request('GET', 'https://api.skinport.com/v1/items', [
            //     'query' => ['app_id' => 730, 'name' => $skin->getName()]
            // ]);
            // return $response->toArray()['available'] ?? false;
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
            // Pour digital, réserver via API
            return $this->reserveDigitalStock($skin);
        }

        return false;
    }

    private function reserveDigitalStock(Skin $skin): bool
    {
        // Simuler réservation
        return true;
    }
}