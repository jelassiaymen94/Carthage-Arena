<?php

namespace App\MessageHandler;

use App\Message\DeliverSkinMessage;
use App\Service\InventoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeliverSkinMessageHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private InventoryService $inventoryService,
    ) {}

    public function __invoke(DeliverSkinMessage $message)
    {
        $skin = $this->entityManager->getRepository(\App\Entity\Skin::class)->find($message->skinId);
        $user = $this->entityManager->getRepository(\App\Entity\User::class)->find($message->userId);

        if ($skin && $user) {
            $this->inventoryService->reserveStock($skin);
            // Additional delivery logic...
        }
    }
}
