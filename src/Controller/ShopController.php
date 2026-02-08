<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShopController extends AbstractController
{
    #[Route('/boutique', name: 'app_shop')]
    public function index(): Response
    {
        return $this->render('shop/index.html.twig', [
            'items' => [
                [
                    'id' => 1,
                    'name' => 'Elderflame Vandal',
                    'game' => 'VALORANT',
                    'rarity' => 'LÉGENDAIRE',
                    'price' => 2175,
                    'image' => 'https://via.placeholder.com/300/1A5C5C/FFFFFF?text=Elderflame',
                    'insufficient' => false,
                ],
                [
                    'id' => 2,
                    'name' => 'Project Ashe',
                    'game' => 'LOL',
                    'rarity' => 'ÉPIQUE',
                    'price' => 1820,
                    'image' => 'https://via.placeholder.com/300/4A4A4A/FFFFFF?text=Project+Ashe',
                    'insufficient' => false,
                ],
                [
                    'id' => 3,
                    'name' => 'Elementalist Lux',
                    'game' => 'LOL',
                    'rarity' => 'ULTIME',
                    'price' => 3250,
                    'image' => 'https://via.placeholder.com/300/2A2A2A/CCCCCC?text=Elementalist',
                    'insufficient' => true,
                ],
                [
                    'id' => 4,
                    'name' => 'Reaver Vandal',
                    'game' => 'VALORANT',
                    'rarity' => 'PREMIUM',
                    'price' => 1775,
                    'image' => 'https://via.placeholder.com/300/3A3A5A/FFFFFF?text=Reaver',
                    'insufficient' => false,
                ],
                [
                    'id' => 5,
                    'name' => 'AK-47 Asiimov',
                    'game' => 'CS2',
                    'rarity' => 'SECRET',
                    'price' => 1200,
                    'image' => 'https://via.placeholder.com/300/F5E6D3/000000?text=Asiimov',
                    'insufficient' => false,
                ],
                [
                    'id' => 6,
                    'name' => 'Spectrum Phantom',
                    'game' => 'VALORANT',
                    'rarity' => 'LÉGENDAIRE',
                    'price' => 2675,
                    'image' => 'https://via.placeholder.com/300/4A5A5A/FFFFFF?text=Spectrum',
                    'insufficient' => true,
                ],
                [
                    'id' => 7,
                    'name' => 'Spirit Blossom Ahri',
                    'game' => 'LOL',
                    'rarity' => 'LÉGENDAIRE',
                    'price' => 1820,
                    'image' => 'https://via.placeholder.com/300/1A1A1A/FFFFFF?text=Spirit+Blossom',
                    'insufficient' => false,
                ],
                [
                    'id' => 8,
                    'name' => 'Prime Classic',
                    'game' => 'VALORANT',
                    'rarity' => 'PREMIUM',
                    'price' => 1775,
                    'image' => 'https://via.placeholder.com/300/2A3A4A/FFFFFF?text=Prime',
                    'insufficient' => false,
                ],
            ],
        ]);
    }

    #[Route('/boutique/{id}', name: 'app_shop_item')]
    public function item(int $id): Response
    {
        $items = [
            1 => [
                'id' => 1,
                'name' => 'Elderflame Vandal',
                'game' => 'VALORANT',
                'rarity' => 'LÉGENDAIRE',
                'price' => 2175,
                'description' => 'Invoquez le pouvoir du dragon ancien avec cette arme légendaire. Chaque tir libère des flammes dévastatrices.',
                'features' => [
                    'Animations de tir uniques avec effets de flammes',
                    'Son personnalisé inspiré du rugissement du dragon',
                    'Effets visuels lors du rechargement',
                    'Finisher exclusif avec dragon de feu'
                ],
                'images' => [
                    'https://via.placeholder.com/800x450/1A5C5C/FFFFFF?text=Elderflame+Main',
                    'https://via.placeholder.com/800x450/2A6C6C/FFFFFF?text=Elderflame+Fire',
                    'https://via.placeholder.com/800x450/3A7C7C/FFFFFF?text=Elderflame+Reload',
                ],
            ],
            2 => [
                'id' => 2,
                'name' => 'Project Ashe',
                'game' => 'LOL',
                'rarity' => 'ÉPIQUE',
                'price' => 1820,
                'description' => 'Skin futuriste de la série PROJECT avec des effets cybernétiques avancés.',
                'features' => [
                    'Modèle 3D entièrement repensé',
                    'Nouvelles animations pour toutes les compétences',
                    'Effets de particules holographiques',
                    'Voix modifiée avec filtre robotique'
                ],
                'images' => [
                    'https://via.placeholder.com/800x450/4A4A4A/FFFFFF?text=Project+Ashe+Main',
                    'https://via.placeholder.com/800x450/5A5A5A/FFFFFF?text=Project+Ashe+Skills',
                    'https://via.placeholder.com/800x450/6A6A6A/FFFFFF?text=Project+Ashe+Ultimate',
                ],
            ],
        ];

        $item = $items[$id] ?? $items[1];

        return $this->render('shop/item.html.twig', [
            'item' => $item,
        ]);
    }
}
