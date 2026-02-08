<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => [
                'totalUsers' => 1247,
                'activeUsers' => 892,
                'totalTournaments' => 45,
                'activeTournaments' => 12,
                'totalRevenue' => '125,450 DT',
                'monthlyRevenue' => '18,200 DT',
                'totalMatches' => 3421,
                'todayMatches' => 28,
            ],
            'recentUsers' => [
                ['name' => 'ShadowSlayer99', 'email' => 'shadow@example.com', 'joined' => 'Il y a 2h', 'status' => 'active'],
                ['name' => 'ProGamer123', 'email' => 'pro@example.com', 'joined' => 'Il y a 5h', 'status' => 'active'],
                ['name' => 'ElitePlayer', 'email' => 'elite@example.com', 'joined' => 'Hier', 'status' => 'pending'],
                ['name' => 'MasterChief', 'email' => 'master@example.com', 'joined' => 'Il y a 2j', 'status' => 'active'],
            ],
            'recentTournaments' => [
                ['name' => 'Carthage Championship S1', 'game' => 'LoL', 'teams' => 12, 'status' => 'active', 'prize' => '10,000 DT'],
                ['name' => 'Valorant Pro Cup', 'game' => 'Valorant', 'teams' => 8, 'status' => 'active', 'prize' => '5,000 DT'],
                ['name' => 'CS:GO Masters', 'game' => 'CS2', 'teams' => 4, 'status' => 'pending', 'prize' => '15,000 DT'],
            ],
            'systemHealth' => [
                'serverStatus' => 'online',
                'cpuUsage' => 45,
                'memoryUsage' => 62,
                'diskUsage' => 38,
                'uptime' => '15 jours',
            ],
        ]);
    }

    #[Route('/users', name: 'admin_users')]
    public function users(): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => [
                ['id' => 1, 'name' => 'ShadowSlayer99', 'email' => 'shadow@example.com', 'role' => 'JOUEUR PRO', 'status' => 'active', 'balance' => '2,450 CP', 'joined' => '2024-01-15'],
                ['id' => 2, 'name' => 'ProGamer123', 'email' => 'pro@example.com', 'role' => 'JOUEUR', 'status' => 'active', 'balance' => '1,200 CP', 'joined' => '2024-02-20'],
                ['id' => 3, 'name' => 'ElitePlayer', 'email' => 'elite@example.com', 'role' => 'JOUEUR PRO', 'status' => 'suspended', 'balance' => '500 CP', 'joined' => '2024-01-10'],
                ['id' => 4, 'name' => 'MasterChief', 'email' => 'master@example.com', 'role' => 'JOUEUR', 'status' => 'active', 'balance' => '3,100 CP', 'joined' => '2024-03-05'],
            ],
        ]);
    }

    #[Route('/tournaments', name: 'admin_tournaments')]
    public function tournaments(): Response
    {
        return $this->render('admin/tournaments/index.html.twig', [
            'tournaments' => [
                ['id' => 1, 'name' => 'Carthage Championship S1', 'game' => 'LoL', 'teams' => 12, 'maxTeams' => 16, 'status' => 'active', 'prize' => '10,000 DT', 'date' => '2024-10-24'],
                ['id' => 2, 'name' => 'Valorant Pro Cup', 'game' => 'Valorant', 'teams' => 8, 'maxTeams' => 8, 'status' => 'active', 'prize' => '5,000 DT', 'date' => '2024-10-21'],
                ['id' => 3, 'name' => 'CS:GO Masters', 'game' => 'CS2', 'teams' => 4, 'maxTeams' => 16, 'status' => 'pending', 'prize' => '15,000 DT', 'date' => '2024-11-05'],
            ],
        ]);
    }

    #[Route('/settings', name: 'admin_settings')]
    public function settings(): Response
    {
        return $this->render('admin/settings/index.html.twig');
    }

    #[Route('/reports', name: 'admin_reports')]
    public function reports(): Response
    {
        return $this->render('admin/reports/index.html.twig', [
            'stats' => [
                'totalRevenue' => '125,450 DT',
                'monthlyRevenue' => '18,200 DT',
                'weeklyRevenue' => '4,500 DT',
                'dailyRevenue' => '850 DT',
                'totalTransactions' => 2847,
                'avgTransactionValue' => '44 DT',
            ],
            'revenueData' => [
                ['month' => 'Jan', 'revenue' => 12500],
                ['month' => 'Fév', 'revenue' => 15800],
                ['month' => 'Mar', 'revenue' => 18200],
                ['month' => 'Avr', 'revenue' => 16500],
                ['month' => 'Mai', 'revenue' => 19800],
                ['month' => 'Juin', 'revenue' => 22400],
            ],
            'topUsers' => [
                ['name' => 'ShadowSlayer99', 'spent' => '5,200 DT', 'tournaments' => 15],
                ['name' => 'ProGamer123', 'spent' => '4,800 DT', 'tournaments' => 12],
                ['name' => 'ElitePlayer', 'spent' => '3,900 DT', 'tournaments' => 10],
            ],
        ]);
    }

    #[Route('/shop', name: 'admin_shop')]
    public function shop(): Response
    {
        return $this->render('admin/shop/index.html.twig', [
            'items' => [
                ['id' => 1, 'name' => 'Elderflame Vandal', 'game' => 'VALORANT', 'price' => 2175, 'sales' => 145, 'revenue' => '315,375 DT', 'stock' => 'unlimited'],
                ['id' => 2, 'name' => 'Project Ashe', 'game' => 'LoL', 'price' => 1820, 'sales' => 98, 'revenue' => '178,360 DT', 'stock' => 'unlimited'],
                ['id' => 3, 'name' => 'AK-47 Asiimov', 'game' => 'CS2', 'price' => 1200, 'sales' => 203, 'revenue' => '243,600 DT', 'stock' => 'unlimited'],
            ],
        ]);
    }

    #[Route('/games', name: 'admin_games')]
    public function games(): Response
    {
        return $this->render('admin/games/index.html.twig', [
            'games' => [
                ['id' => 1, 'name' => 'Valorant', 'publisher' => 'Riot Games', 'category' => 'FPS', 'tournaments' => 15, 'players' => 450, 'status' => 'active', 'icon' => '🎯'],
                ['id' => 2, 'name' => 'League of Legends', 'publisher' => 'Riot Games', 'category' => 'MOBA', 'tournaments' => 12, 'players' => 380, 'status' => 'active', 'icon' => '⚔️'],
                ['id' => 3, 'name' => 'CS:GO 2', 'publisher' => 'Valve', 'category' => 'FPS', 'tournaments' => 8, 'players' => 290, 'status' => 'active', 'icon' => '🔫'],
                ['id' => 4, 'name' => 'Rocket League', 'publisher' => 'Psyonix', 'category' => 'Sports', 'tournaments' => 5, 'players' => 120, 'status' => 'active', 'icon' => '🚗'],
                ['id' => 5, 'name' => 'Apex Legends', 'publisher' => 'EA', 'category' => 'Battle Royale', 'tournaments' => 3, 'players' => 85, 'status' => 'inactive', 'icon' => '🎮'],
            ],
        ]);
    }
}
