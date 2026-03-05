<?php

namespace App\Service;

use App\Entity\Merch;
use App\Entity\Purchase;
use App\Entity\Skin;
use App\Entity\User;
use App\Entity\UserSkin;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class PurchaseService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    /**
     * Buy merch using CP balance (existing flow).
     */
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

    /**
     * Fulfills an item after a successful Stripe payment.
     * Called from the webhook handler.
     */
    public function fulfillStripeOrder(string $itemId, string $itemType, User $user): void
    {
        if ($itemType === 'skin') {
            $skin = $this->em->getRepository(Skin::class)->find($itemId);
            if (!$skin) {
                throw new Exception("Skin not found: $itemId");
            }

            // Check if user already owns this skin (prevent double-delivery)
            $existing = $this->em->getRepository(UserSkin::class)->findOneBy([
                'user' => $user,
                'skin' => $skin,
            ]);
            if ($existing) {
                return; // Already delivered
            }

            if ($skin->getStock() > 0) {
                $skin->setStock($skin->getStock() - 1);
            }

            $userSkin = new UserSkin();
            $userSkin->setUser($user);
            $userSkin->setSkin($skin);
            $userSkin->setStatus('active');
            $this->em->persist($userSkin);
            $this->em->flush();

        } elseif ($itemType === 'merch') {
            $merch = $this->em->getRepository(Merch::class)->find($itemId);
            if (!$merch) {
                throw new Exception("Merch not found: $itemId");
            }

            if ($merch->getStock() < 1) {
                throw new Exception("Out of stock: $itemId");
            }

            $merch->setStock($merch->getStock() - 1);

            $purchase = new Purchase();
            $purchase->setMerch($merch);
            $purchase->setUser($user);
            $purchase->setQuantity(1);
            $purchase->calculateTotalPrice();

            $this->em->persist($purchase);
            $this->em->flush();
        }
    }
}