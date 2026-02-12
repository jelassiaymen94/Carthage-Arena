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
    public function index(TournoiRepository $tournoiRepository): Response
    {
        return $this->render('tournament/index.html.twig', [
            'tournois' => $tournoiRepository->findAll(),
        ]);
    }

    #[Route('/tournois/{id}', name: 'app_tournament_details')]
    public function details(Tournoi $tournoi): Response
    {
        return $this->render('tournament/details.html.twig', [
            'tournoi' => $tournoi,
        ]);
    }
}
