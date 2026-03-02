<?php

namespace App\Controller;

use App\Entity\Tournoi;
use App\Form\TournoiType;
use App\Repository\TournoiRepository;
use App\Repository\TeamRepository;
use App\Service\MatchGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/tournoi')]
final class TournoiController extends AbstractController
{
    #[Route(name: 'app_tournoi_index', methods: ['GET'])]
    public function index(TournoiRepository $tournoiRepository): Response
    {
        return $this->render('tournoi/index.html.twig', [
            'tournois' => $tournoiRepository->findAll(),
        ]);
    }

    // ── Calendar ──────────────────────────────────────────────────────────────

    #[Route('/calendar', name: 'app_tournoi_calendar', methods: ['GET'])]
    public function calendar(): Response
    {
        return $this->render('tournoi/calendar.html.twig');
    }

    #[Route('/calendar/events', name: 'app_tournoi_calendar_events', methods: ['GET'])]
    public function calendarEvents(TournoiRepository $tournoiRepository): JsonResponse
    {
        $tournois = $tournoiRepository->findAll();
        $events = [];

        $colorMap = [
            'upcoming' => '#3b82f6',   // blue
            'ongoing' => '#22c55e',   // green
            'completed' => '#6b7280',   // gray
            'cancelled' => '#ef4444',   // red
        ];

        foreach ($tournois as $tournoi) {
            if (!$tournoi->getDateDebut() || !$tournoi->getDateFin()) {
                continue;
            }

            $statusValue = $tournoi->getStatus()->value;
            $color = $colorMap[$statusValue] ?? '#a855f7';

            // FullCalendar end date is exclusive, so add 1 day for all-day events
            $endDate = $tournoi->getDateFin()->modify('+1 day');

            $events[] = [
                'id' => (string) $tournoi->getId(),
                'title' => $tournoi->getNom(),
                'start' => $tournoi->getDateDebut()->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'url' => $this->generateUrl('app_tournoi_show', ['id' => $tournoi->getId()]),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'status' => $tournoi->getStatus()->label ?? $statusValue,
                    'type' => $tournoi->getType()?->label ?? '',
                    'teams' => $tournoi->getNbEquipesMax(),
                    'prize' => $tournoi->getPrizePool(),
                ],
            ];
        }

        return new JsonResponse($events);
    }

    #[Route('/new', name: 'app_tournoi_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tournoi = new Tournoi();
        $form = $this->createForm(TournoiType::class, $tournoi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tournoi);
            $entityManager->flush();

            return $this->redirectToRoute('app_tournoi_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tournoi/new.html.twig', [
            'tournoi' => $tournoi,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tournoi_show', methods: ['GET'])]
    public function show(Tournoi $tournoi): Response
    {
        return $this->render('tournoi/show.html.twig', [
            'tournoi' => $tournoi,
            'teams' => $tournoi->getTeams(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tournoi_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TournoiType::class, $tournoi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le tournoi "' . $tournoi->getNom() . '" a été mis à jour.');
            return $this->redirectToRoute('admin_tournaments', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tournoi/edit.html.twig', [
            'tournoi' => $tournoi,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/generate-matches', name: 'app_tournoi_generate_matches', methods: ['POST'])]
    public function generateMatches(
        Tournoi $tournoi,
        MatchGeneratorService $matchGenerator,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        // CSRF protection
        if (!$this->isCsrfTokenValid('generate-matches-' . $tournoi->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_tournoi_edit', ['id' => $tournoi->getId()]);
        }

        try {
            // Remove existing matches if any
            foreach ($tournoi->getMatches() as $match) {
                $entityManager->remove($match);
            }
            $entityManager->flush();

            // Generate new matches
            $matches = $matchGenerator->generateMatches($tournoi);

            // Persist all matches
            foreach ($matches as $match) {
                $entityManager->persist($match);
            }
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                '%d matchs ont été générés avec succès pour le tournoi "%s".',
                count($matches),
                $tournoi->getNom()
            ));
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la génération des matchs.');
        }

        return $this->redirectToRoute('app_tournoi_edit', ['id' => $tournoi->getId()]);
    }

    #[Route('/{id}', name: 'app_tournoi_delete', methods: ['POST'])]
    public function delete(Request $request, Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tournoi->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tournoi);
            $entityManager->flush();
            $this->addFlash('success', 'Le tournoi a été supprimé.');
        }

        return $this->redirectToRoute('admin_tournaments', [], Response::HTTP_SEE_OTHER);
    }
}
