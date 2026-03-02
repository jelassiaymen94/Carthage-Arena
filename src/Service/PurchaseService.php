<?php

namespace App\Service;

use App\Entity\Purchase;
use App\Entity\Merch;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class PurchaseService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function buy(Merch $merch, User $user, int $quantity): Purchase
    {
        if ($quantity <= 0) {
            throw new Exception("Quantity must be positive.");
        }

        if ($merch->getStock() < $quantity) {
            throw new Exception("Not enough stock available.");
        }

        // Decrease stock
        $merch->setStock($merch->getStock() - $quantity);

        $purchase = new Purchase();
        $purchase->setMerch($merch);
        $purchase->setUser($user);
        $purchase->setQuantity($quantity);
        $purchase->calculateTotalPrice();

        $this->em->persist($purchase);
        $this->em->flush();

        return $purchase;
    }
}
