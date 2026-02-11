<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profil', name: 'app_profile')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $profile = $user->getProfile();

        return $this->render('profile/index.html.twig', [
            'user' => [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'role' => in_array('ROLE_ADMIN', $user->getRoles()) ? 'ADMINISTRATEUR' : 'JOUEUR',
                'balance' => number_format($user->getBalance(), 0, ',', ','),
                'avatar' => $profile && $profile->getAvatarUrl() ? '/uploads/avatars/' . $profile->getAvatarUrl() : 'https://i.pravatar.cc/300?img=12',
                'rank' => 'Unranked', // This would come from a real ranking system later
                'rankProgress' => 0,
                'level' => 1,
                'joinDate' => $user->getCreatedAt()->format('F Y'),
                'country' => 'Tunisie', // Could be added to Profile entity later
                'bio' => $profile ? $profile->getBio() : 'Aucune biographie.',
            ],
            // Keep mock data for stats/matches/achievements as they are not part of the current plan's scope for implementation
            'stats' => [
                'matchesPlayed' => 0,
                'wins' => 0,
                'losses' => 0,
                'winRate' => 0,
                'tournamentsWon' => 0,
                'totalEarnings' => '0 DT',
            ],
            'recentMatches' => [],
            'achievements' => [],
        ]);
    }
}
