<?php

namespace App\Controller;

use App\Entity\Tournoi;
use App\Repository\TournoiRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TournamentController extends AbstractController
{
    #[Route('/tournois', name: 'app_tournaments')]
    public function index(TournoiRepository $tournoiRepository, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        $filter = $request->query->get('filter');
        $tournois = $tournoiRepository->findByFilter($filter, $this->getUser());

        return $this->render('tournament/index.html.twig', [
            'tournois' => $tournois,
            'currentFilter' => $filter,
        ]);
    }

    #[Route('/tournois/{id}', name: 'app_tournament_details')]
    public function details(Tournoi $tournoi): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        $userTeam = null;
        $isTeamComplete = false;

        if ($user) {
            foreach ($user->getTeamMemberships() as $membership) {
                $userTeam = $membership->getTeam();
                if ($userTeam) {
                    $isTeamComplete = $userTeam->getMembers()->count() >= 5;
                    break;
                }
            }
        }

        return $this->render('tournament/details.html.twig', [
            'tournoi' => $tournoi,
            'userTeam' => $userTeam,
            'isTeamComplete' => $isTeamComplete,
        ]);
    }
}
