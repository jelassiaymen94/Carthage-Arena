<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Skin;
use App\Entity\UserSkin;
use App\Repository\MerchRepository;
use App\Repository\SkinRepository;
use App\Service\InventoryService;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
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

        // Normalize data for view
        $viewItem = [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'description' => $item->getDescription(),
            'price' => $item->getPrice(),
            'imageUrl' => $item->getImageUrl(),
            'game' => $item->getGame() ? $item->getGame()->getName() : 'Autre',
            'type' => $type,
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

    #[Route('/boutique/acheter/{id}', name: 'app_shop_buy', methods: ['POST'])]
    public function buy(
        string $id,
        SkinRepository $skinRepository,
        EntityManagerInterface $entityManager,
        PaymentService $paymentService,
        InventoryService $inventoryService
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $skin = $skinRepository->find($id);
        if (!$skin) {
            throw $this->createNotFoundException('Skin non trouvé');
        }

        if (!$inventoryService->checkStock($skin)) {
            $this->addFlash('error', 'Stock épuisé');
            return $this->redirectToRoute('app_shop_item', ['id' => $id]);
        }

        // Vérifier si l'utilisateur a suffisamment de CP
        if ($user->getBalance() >= $skin->getPrice()) {
            // Déduire du solde et valider l'achat
            $user->setBalance($user->getBalance() - $skin->getPrice());
            
            // Créer un enregistrement de propriété du skin
            $userSkin = new UserSkin();
            $userSkin->setUser($user);
            $userSkin->setSkin($skin);
            $userSkin->setStatus('active');
            $entityManager->persist($userSkin);
            $entityManager->flush();
            
            // Réserver le stock
            $inventoryService->reserveStock($skin);
            
            $this->addFlash('success', 'Skin acheté avec succès! Vérifiez votre historique d\'achat.');
            return $this->redirectToRoute('app_profile_historique_achat');
        }

        // Solde insuffisant - proposer un paiement par carte
        return $this->render('shop/payment.html.twig', [
            'skin' => $skin,
        ]);
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