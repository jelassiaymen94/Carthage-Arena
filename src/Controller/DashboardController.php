<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('dashboard/index.html.twig', [
            'user' => [
                'name' => $user ? $user->getUsername() : 'Invité',
                'role' => 'JOUEUR PRO',
                'balance' => $user ? number_format($user->getBalance(), 0, ',', ',') : '0',
                'avatar' => $user && $user->getAvatar() ? '/uploads/avatars/' . $user->getAvatar() : 'https://i.pravatar.cc/150?img=12',
                'rank' => 'Diamond II',
                'rankProgress' => 75,
                'matchesPlayed' => 124,
                'winRate' => 62,
            ],
            'upcomingMatches' => [
                [
                    'tournament' => 'VALORANT TOURNAMENT',
                    'opponent' => 'Team Liquid',
                    'date' => 'Oct 24, 2000',
                    'format' => 'Best of 3',
                    'maps' => 'Haven, Ascent, Bind',
                    'logo' => 'https://via.placeholder.com/48/FF0022/FFFFFF?text=TL',
                ],
                [
                    'tournament' => 'LEAGUE OF LEGENDS',
                    'opponent' => 'G2 Esports',
                    'date' => 'Oct 26, 18:30',
                    'format' => 'Best of 1',
                    'prize' => '600 CP',
                    'logo' => 'https://via.placeholder.com/48/000000/FFFFFF?text=G2',
                ],
                [
                    'tournament' => 'CS:GO 2',
                    'opponent' => 'Cloud9',
                    'date' => 'Oct 28, 07:00',
                    'format' => 'Best of 3',
                    'region' => 'NA East',
                    'logo' => 'https://via.placeholder.com/48/0099CC/FFFFFF?text=C9',
                ],
            ],
        ]);
    }
}
