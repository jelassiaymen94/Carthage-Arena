<?php

namespace App\Controller\Api;

use App\Repository\PurchaseRepository;
use App\Repository\MerchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/stats', name: 'api_stats_')]
class StatsController extends AbstractController
{
    #[Route('/top-merch', name: 'top_merch', methods: ['GET'])]
    public function topMerch(PurchaseRepository $purchaseRepo, MerchRepository $merchRepo): JsonResponse
    {
        $qb = $purchaseRepo->createQueryBuilder('p')
            ->select('IDENTITY(p.merch) as merchId, COUNT(p.id) as total')
            ->groupBy('p.merch')
            ->orderBy('total', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        $topMerch = [];
        foreach ($qb as $entry) {
            $merch = $merchRepo->find($entry['merchId']);
            if ($merch) {
                $topMerch[] = [
                    'id' => $merch->getId(),
                    'name' => $merch->getName(),
                    'price' => $merch->getPrice(),
                    'stock' => $merch->getStock(),
                    'imageUrl' => $merch->getImageUrl(),
                    'totalPurchased' => (int) $entry['total'],
                ];
            }
        }

        return $this->json($topMerch);
    }
}