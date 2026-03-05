<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Skin;
use App\Entity\UserSkin;
use App\Repository\MerchRepository;
use App\Repository\SkinRepository;
use App\Service\InventoryService;
use App\Service\PaymentService;
use App\Service\PurchaseService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ShopController extends AbstractController
{
    #[Route('/boutique', name: 'app_shop')]
    public function index(
        SkinRepository $skinRepository,
        MerchRepository $merchRepository,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $userBalance = $user ? $user->getBalance() : 0;

        $searchTerm = $request->query->get('q');
        $gameFilter = $request->query->get('game');
        $sort = $request->query->get('sort');

        $skins = $skinRepository->search($searchTerm, $gameFilter, $sort);
        $merch = $merchRepository->search($searchTerm, $gameFilter, $sort);

        $items = [];
        foreach ($skins as $skin) {
            $items[] = [
                'id' => $skin->getId(),
                'name' => $skin->getName(),
                'game' => $skin->getGame() ? $skin->getGame()->getName() : 'N/A',
                'rarity' => $skin->getRarity() ? $skin->getRarity()->value : 'COMMON',
                'price' => $skin->getPrice(),
                'imageUrl' => $skin->getImageUrl(),
                'type' => 'skin',
                'insufficient' => $userBalance < $skin->getPrice(),
            ];
        }

        foreach ($merch as $m) {
            $items[] = [
                'id' => $m->getId(),
                'name' => $m->getName(),
                'game' => $m->getGame() ? $m->getGame()->getName() : 'Autre',
                'rarity' => 'MERCH',
                'price' => $m->getPrice(),
                'imageUrl' => $m->getImageUrl(),
                'type' => 'merch',
                'insufficient' => $userBalance < $m->getPrice(),
            ];
        }

        // Apply global sorting if requested
        if ($sort === 'price_asc') {
            usort($items, fn($a, $b) => $a['price'] <=> $b['price']);
        } elseif ($sort === 'price_desc') {
            usort($items, fn($a, $b) => $b['price'] <=> $a['price']);
        }

        $featuredItem = null;
        if (!empty($items)) {
            $featuredItem = $items[array_rand($items)];
        }

        return $this->render('shop/index.html.twig', [
            'items' => $items,
            'featuredItem' => $featuredItem,
            'currentSearch' => $searchTerm,
            'currentGame' => $gameFilter,
            'currentSort' => $sort,
        ]);
    }

    #[Route('/boutique/{id}', name: 'app_shop_item')]
    public function item(string $id, SkinRepository $skinRepository, MerchRepository $merchRepository): Response
    {
        // Try to find in Skins first
        $item = $skinRepository->find($id);
        $type = 'skin';

        if (!$item) {
            $item = $merchRepository->find($id);
            $type = 'merch';
        }

        if (!$item) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        /** @var User|null $user */
        $user = $this->getUser();
        $userBalance = $user ? $user->getBalance() : 0;

        // Normalize data for view
        $viewItem = [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'description' => $item->getDescription(),
            'price' => $item->getPrice(),
            'imageUrl' => $item->getImageUrl(),
            'game' => $item->getGame() ? $item->getGame()->getName() : 'Autre',
            'type' => $type,
            'insufficient' => $userBalance < $item->getPrice(),
            'userBalance' => $userBalance,
        ];

        if ($type === 'skin') {
            $viewItem['rarity'] = $item->getRarity() ? $item->getRarity()->value : 'COMMON';
        } else {
            $viewItem['rarity'] = 'MERCH';
        }

        return $this->render('shop/item.html.twig', [
            'item' => $viewItem,
        ]);
    }

    /**
     * Unified buy endpoint for both skins and merch.
     * If user has enough balance → deduct balance.
     * If insufficient balance → create Stripe Checkout session and redirect.
     */
    #[Route('/boutique/acheter/{id}', name: 'app_shop_buy', methods: ['POST'])]
    public function buy(
        string $id,
        Request $request,
        SkinRepository $skinRepository,
        MerchRepository $merchRepository,
        EntityManagerInterface $entityManager,
        PaymentService $paymentService,
        PurchaseService $purchaseService,
        InventoryService $inventoryService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Detect item type from hidden field or try both repos
        $itemType = $request->request->get('item_type', '');
        $item = null;

        if ($itemType === 'skin') {
            $item = $skinRepository->find($id);
        } elseif ($itemType === 'merch') {
            $item = $merchRepository->find($id);
        } else {
            // Fallback: try skin first
            $item = $skinRepository->find($id);
            if ($item) {
                $itemType = 'skin';
            } else {
                $item = $merchRepository->find($id);
                $itemType = 'merch';
            }
        }

        if (!$item) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        // Check stock
        if ($itemType === 'skin' && !$inventoryService->checkStock($item)) {
            $this->addFlash('error', 'Stock épuisé pour cet article.');
            return $this->redirectToRoute('app_shop_item', ['id' => $id]);
        }

        $price = $item->getPrice();

        // ── Path 1: User has enough CP balance ──────────────────────────────
        if ($user->getBalance() >= $price) {
            $user->setBalance($user->getBalance() - $price);

            if ($itemType === 'skin') {
                $userSkin = new UserSkin();
                $userSkin->setUser($user);
                $userSkin->setSkin($item);
                $userSkin->setStatus('active');
                $entityManager->persist($userSkin);
                $inventoryService->reserveStock($item);
            } else {
                $purchaseService->buy($item, $user, 1);
            }

            $entityManager->flush();

            $this->addFlash('success', '🎉 Achat réussi ! Consultez votre historique.');
            return $this->redirectToRoute('app_profile_historique_achat');
        }

        // ── Path 2: Insufficient balance → Stripe Checkout ──────────────────
        try {
            $checkoutUrl = $paymentService->createCheckoutSession($item, $user, $itemType);
            return $this->redirect($checkoutUrl);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création du paiement : ' . $e->getMessage());
            return $this->redirectToRoute('app_shop_item', ['id' => $id]);
        }
    }

    /**
     * Stripe Checkout success callback.
     */
    #[Route('/boutique/stripe/success', name: 'app_shop_stripe_success', methods: ['GET'], priority: 10)]
    public function stripeSuccess(Request $request, string $stripeSecretKey): Response
    {
        $sessionId = $request->query->get('session_id');

        $sessionData = null;
        if ($sessionId && $stripeSecretKey) {
            try {
                $stripe = new StripeClient($stripeSecretKey);
                $session = $stripe->checkout->sessions->retrieve($sessionId, [
                    'expand' => ['line_items'],
                ]);
                $sessionData = [
                    'amount' => ($session->amount_total / 100),
                    'currency' => strtoupper($session->currency),
                    'items' => $session->line_items->data ?? [],
                ];
            } catch (\Exception $e) {
                // Still show success page even if retrieval fails
            }
        }

        return $this->render('shop/stripe_success.html.twig', [
            'session' => $sessionData,
        ]);
    }

    /**
     * Stripe Checkout cancel callback.
     */
    #[Route('/boutique/stripe/cancel', name: 'app_shop_stripe_cancel', methods: ['GET'], priority: 10)]
    public function stripeCancel(Request $request): Response
    {
        $id = $request->query->get('id');
        $this->addFlash('warning', 'Paiement annulé. Vous pouvez réessayer quand vous voulez.');

        if ($id) {
            return $this->redirectToRoute('app_shop_item', ['id' => $id]);
        }

        return $this->redirectToRoute('app_shop');
    }

    #[Route('/api/shop/stock/{id}', name: 'app_shop_stock', methods: ['GET'])]
    public function getStock(string $id, SkinRepository $skinRepository, InventoryService $inventoryService): Response
    {
        $skin = $skinRepository->find($id);
        if (!$skin) {
            return $this->json(['error' => 'Skin not found'], 404);
        }

        return $this->json([
            'available' => $inventoryService->checkStock($skin),
            'stock' => $skin->getStock(),
        ]);
    }
}
