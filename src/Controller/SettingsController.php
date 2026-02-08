<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends AbstractController
{
    #[Route('/parametres', name: 'app_settings')]
    public function index(): Response
    {
        return $this->render('settings/index.html.twig', [
            'user' => [
                'name' => 'ShadowSlayer99',
                'email' => 'shadowslayer@carthage.gg',
                'avatar' => 'https://i.pravatar.cc/300?img=12',
                'country' => 'Tunisie',
                'language' => 'Français',
                'timezone' => 'Africa/Tunis',
            ],
        ]);
    }
}
