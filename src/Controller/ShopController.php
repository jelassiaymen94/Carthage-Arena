<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\MerchRepository;
use App\Repository\SkinRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShopController extends AbstractController
{
    #[Route('/boutique', name: 'app_shop')]
    public function index(SkinRepository $skinRepository, MerchRepository $merchRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $userBalance = $user ? $user->getBalance() : 0;

        $skins = $skinRepository->findAll();
        $merch = $merchRepository->findAll();

        $items = [];
        $featuredItem = null;

        // Combine for featured item selection
        $allItems = array_merge($skins, $merch);
        if (!empty($allItems)) {
            $featuredItem = $allItems[array_rand($allItems)];
        }

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
                'rarity' => 'MERCH', // Pseudo-rarity for display
                'price' => $m->getPrice(),
                'imageUrl' => $m->getImageUrl(),
                'type' => 'merch',
                'insufficient' => $userBalance < $m->getPrice(),
            ];
        }

        return $this->render('shop/index.html.twig', [
            'items' => $items,
            'featuredItem' => $featuredItem,
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
}
