<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profil', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('profile/index.html.twig', [
            'user' => [
                'name' => $user->getUsername(),
                'email' => $user->getEmail(),
                'role' => 'JOUEUR PRO',
                'balance' => number_format($user->getBalance(), 0, ',', ','),
                'avatar' => $user->getAvatar() ? '/uploads/avatars/' . $user->getAvatar() : 'https://i.pravatar.cc/300?img=12',
                'rank' => 'Diamond II',
                'rankProgress' => 75,
                'level' => 42,
                'joinDate' => $user->getCreatedAt()->format('F Y'),
                'country' => 'Tunisie',
                'bio' => 'Joueur professionnel passionné par les jeux compétitifs. Spécialisé en Valorant et League of Legends.',
            ],
            'stats' => [
                'matchesPlayed' => 124,
                'wins' => 77,
                'losses' => 47,
                'winRate' => 62,
                'tournamentsWon' => 8,
                'totalEarnings' => '45,000 DT',
            ],
            'recentMatches' => [
                ['game' => 'Valorant', 'result' => 'Victoire', 'score' => '13-7', 'date' => 'Il y a 2h'],
                ['game' => 'League of Legends', 'result' => 'Défaite', 'score' => '12-15', 'date' => 'Il y a 5h'],
                ['game' => 'Valorant', 'result' => 'Victoire', 'score' => '13-9', 'date' => 'Hier'],
                ['game' => 'CS:GO 2', 'result' => 'Victoire', 'score' => '16-12', 'date' => 'Il y a 2j'],
            ],
            'achievements' => [
                ['name' => 'Champion de la Saison 1', 'icon' => '🏆', 'date' => 'Sept 2024'],
                ['name' => '100 Victoires', 'icon' => '⭐', 'date' => 'Août 2024'],
                ['name' => 'Meilleur Joueur', 'icon' => '👑', 'date' => 'Juil 2024'],
                ['name' => 'Série de 10 Victoires', 'icon' => '🔥', 'date' => 'Juin 2024'],
            ],
        ]);
    }
}
