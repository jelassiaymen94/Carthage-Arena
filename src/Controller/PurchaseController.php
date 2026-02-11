<?php
namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\Player;
use App\Entity\Product;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/purchases')]
class PurchaseController
{
    #[Route('', methods: ['POST'])]
    public function buy(EntityManagerInterface $em): JsonResponse
    {
        $player = $em->getRepository(Player::class)->find(1);
        $product = $em->getRepository(Product::class)->find(1);

        if ($player->getPointsBalance() < $product->getPricePoints()) {
            return new JsonResponse(["error" => "Not enough points"], 400);
        }

        $purchase = new Purchase();
        $purchase->setPlayer($player);
        $purchase->setProduct($product);
        $purchase->setPurchaseDate(new DateTime());
        $purchase->setQuantity(1);
        $purchase->setTotalAmount($product->getPricePoints());

        $player->setPointsBalance(
            $player->getPointsBalance() - $product->getPricePoints()
        );

        $em->persist($purchase);
        $em->flush();

        return new JsonResponse(["message" => "Purchase successful"]);
    }
}
?>