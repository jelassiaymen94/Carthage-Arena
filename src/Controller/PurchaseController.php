<?php

namespace App\Controller;

use App\Repository\MerchRepository;
use App\Service\PurchaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PurchaseController extends AbstractController
{
    #[Route('/api/purchase/{id}', name: 'buy_merch', methods: ['POST'])]
    public function buy(
        string $id,
        Request $request,
        MerchRepository $merchRepository,
        PurchaseService $purchaseService
    ): JsonResponse {

        $user = $this->getUser();
        if (!$user) return $this->json(['error' => 'Unauthorized'], 401);

        $merch = $merchRepository->find($id);
        if (!$merch) return $this->json(['error' => 'Merch not found'], 404);

        $data = json_decode($request->getContent(), true);
        $quantity = $data['quantity'] ?? 1;

        try {
            $purchase = $purchaseService->buy($merch, $user, $quantity);

            return $this->json([
                'message' => 'Purchase successful',
                'totalPrice' => $purchase->getTotalPrice()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}