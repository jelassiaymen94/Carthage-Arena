<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TournamentController extends AbstractController
{
    #[Route('/tournois', name: 'app_tournaments')]
    public function index(): Response
    {
        return $this->render('tournament/index.html.twig');
    }

    #[Route('/tournois/{id}', name: 'app_tournament_details')]
    public function details(int $id): Response
    {
        return $this->render('tournament/details.html.twig', [
            'tournament' => [
                'id' => $id,
                'name' => 'CARTHAGE CHAMPIONSHIP',
                'subtitle' => 'SEASON 1',
                'status' => 'OUVERT',
                'game' => '5V5 SUMMONERS RIFT',
                'season' => 'Saison 1 - Phase Éliminatoire',
                'description' => 'Rejoignez l\'arène ultime pour la première saison du championnat. Prouvez votre valeur, dominez la faille et repartez avec le titre de champion.',
                'prizePool' => '10k DT',
                'format' => 'Élimination simple, BO3 jusqu\'aux demi-finales, Finale en BO5',
                'retard' => 'Tolérance de retard avant forfait automatique',
                'platform' => 'Plateforme de tournoi: Discord obligatoire',
                'teams' => [
                    'Équipes' => '6 joueurs titulaires + 2 remplaçants max',
                ],
                'inscriptions' => 12,
                'maxInscriptions' => 16,
                'deadline' => '2j 14h 30m',
                'isComplete' => true,
                'isFree' => true,
                'bracket' => [
                    'demiFinale1' => [
                        ['name' => 'K Corp', 'score' => 1],
                        ['name' => 'Solary', 'score' => 0],
                    ],
                    'demiFinale2' => [
                        ['name' => 'Vitality', 'score' => 0],
                        ['name' => 'BDS', 'score' => 1],
                    ],
                    'finale' => [
                        ['name' => 'K Corp', 'score' => null],
                        ['name' => 'BDS', 'score' => null],
                    ],
                ],
                'registeredTeams' => [
                    ['name' => 'Karmine Corp', 'members' => 6, 'tag' => 'KC'],
                    ['name' => 'Vitality Bee', 'members' => 6, 'tag' => 'VIT'],
                    ['name' => 'BDS Academy', 'members' => 5, 'tag' => 'BDS'],
                    ['name' => 'Solary', 'members' => 6, 'tag' => 'SLY'],
                    ['name' => 'Fnatic TQ', 'members' => 5, 'tag' => 'FNC'],
                ],
            ],
        ]);
    }
}
