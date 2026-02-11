<?php

namespace App\Controller;

use App\Entity\Skin;
use App\Entity\Merch;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/products')]
class ProductController
{
    #[Route('', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $products = $em->getRepository(Product::class)->findAll();
        return new JsonResponse($products);
    }

    #[Route('/skin', methods: ['POST'])]
    public function createSkin(EntityManagerInterface $em): JsonResponse
    {
        $skin = new Skin();
        $skin->setName("Dragon Fire");
        $skin->setDescription("Epic skin");
        $skin->setPricePoints(200);
        $skin->setRarity("Epic");
        $skin->setImage("dragon.png");

        $em->persist($skin);
        $em->flush();

        return new JsonResponse(["message" => "Skin created"]);
    }

    #[Route('/merch', methods: ['POST'])]
    public function createMerch(EntityManagerInterface $em): JsonResponse
    {
        $merch = new Merch();
        $merch->setName("Carthage Hoodie");
        $merch->setDescription("Official hoodie");
        $merch->setPricePoints(500);
        $merch->setStock(20);
        $merch->setSize("L");

        $em->persist($merch);
        $em->flush();

        return new JsonResponse(["message" => "Merch created"]);
    }
}
?>