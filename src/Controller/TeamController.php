<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TeamController extends AbstractController
{
    #[Route('/equipe', name: 'app_team')]
    public function index(): Response
    {
        // Mock data: User is a leader of "Carthage Eagles"
        // Toggle $hasTeam to false to see the "Create/Join" view in a real app logic
        $hasTeam = true;

        if (!$hasTeam) {
            return $this->render('team/no_team.html.twig');
        }

        return $this->render('team/index.html.twig', [
            'team' => [
                'name' => 'Carthage Eagles',
                'tag' => 'EGLS',
                'logo' => 'https://via.placeholder.com/150/FF0022/FFFFFF?text=EGLS',
                'created_at' => '15 Jan 2024',
                'description' => 'Équipe compétitive Valorant & LoL. Objectif: Top 1 Tunisie.',
                'leader' => 'ShadowSlayer99',
                'level' => 15,
                'wins' => 45,
                'losses' => 12,
                'tournaments_won' => 3,
                'members' => [
                    ['name' => 'ShadowSlayer99', 'role' => 'Leader', 'avatar' => 'https://i.pravatar.cc/150?u=1', 'status' => 'online', 'rank' => 'Diamond II'],
                    ['name' => 'ViperStrike', 'role' => 'Co-Leader', 'avatar' => 'https://i.pravatar.cc/150?u=2', 'status' => 'offline', 'rank' => 'Ascendant I'],
                    ['name' => 'PixelMage', 'role' => 'Membre', 'avatar' => 'https://i.pravatar.cc/150?u=3', 'status' => 'online', 'rank' => 'Platinum III'],
                    ['name' => 'TankZilla', 'role' => 'Membre', 'avatar' => 'https://i.pravatar.cc/150?u=4', 'status' => 'in-game', 'rank' => 'Diamond I'],
                    ['name' => 'HealerPro', 'role' => 'Membre', 'avatar' => 'https://i.pravatar.cc/150?u=5', 'status' => 'offline', 'rank' => 'Gold I'],
                ],
                'invites' => [
                    ['code' => 'X7K-9P2', 'expires' => '24h'],
                ],
                'upcoming_tournaments' => [
                    ['name' => 'Carthage Championship S1', 'date' => '24 Oct 2024', 'status' => 'Registered'],
                ]
            ],
            'is_leader' => true, // Simulate user being the leader
        ]);
    }

    #[Route('/equipe/creer', name: 'app_team_create')]
    public function create(): Response
    {
        return $this->render('team/create.html.twig');
    }

    #[Route('/equipe/rejoindre', name: 'app_team_join')]
    public function join(): Response
    {
        return $this->render('team/join.html.twig');
    }
}
