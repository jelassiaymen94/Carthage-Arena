<?php

namespace App\Controller;

use App\Repository\SkinRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(SkinRepository $skinRepository): Response
    {
        $user = $this->getUser();

        // Get a random skin or latest for recommendation
        $skins = $skinRepository->findAll();
        $featuredSkin = null;
        if (!empty($skins)) {
            $featuredSkin = $skins[array_rand($skins)];
        }

        return $this->render('dashboard/index.html.twig', [
            'user' => [
                // Rank and stats are not yet in User entity, so we keep them hardcoded for now
                'rank' => 'Diamond II',
                'rankProgress' => 75,
                'matchesPlayed' => 124,
                'winRate' => 62,
            ],
            'featuredSkin' => $featuredSkin,
            'upcomingMatches' => [
                [
                    'tournament' => 'VALORANT TOURNAMENT',
                    'opponent' => 'Team Liquid',
                    'date' => 'Oct 24, 20:00',
                    'format' => 'Best of 3',
                    'maps' => 'Haven, Ascent, Bind',
                    'logo' => 'https://ui-avatars.com/api/?name=TL&background=0D47A1&color=fff',
                ],
                [
                    'tournament' => 'LEAGUE OF LEGENDS',
                    'opponent' => 'G2 Esports',
                    'date' => 'Oct 26, 18:30',
                    'format' => 'Best of 1',
                    'prize' => '600 CP',
                    'logo' => 'https://ui-avatars.com/api/?name=G2&background=000&color=fff',
                ],
                [
                    'tournament' => 'CS:GO 2',
                    'opponent' => 'Cloud9',
                    'date' => 'Oct 28, 07:00',
                    'format' => 'Best of 3',
                    'region' => 'NA East',
                    'logo' => 'https://ui-avatars.com/api/?name=C9&background=0099CC&color=fff',
                ],
            ],
        ]);
    }
}
