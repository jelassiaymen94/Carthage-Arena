<?php

namespace App\Controller\Admin;

use App\Entity\Reclamation;
use App\Entity\ReclamationResponse;
use App\Enum\ReclamationPriority;
use App\Enum\ReclamationStatus;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reclamations')]
#[IsGranted('ROLE_ADMIN')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'app_admin_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository, Request $request): Response
    {
        $status = $request->query->get('status');
        $priority = $request->query->get('priority');

        $criteria = [];
        if ($status) {
            $criteria['status'] = ReclamationStatus::tryFrom($status);
        }
        if ($priority) {
            $criteria['priority'] = ReclamationPriority::tryFrom($priority);
        }

        // Remove null values
        $criteria = array_filter($criteria);

        $reclamations = $reclamationRepository->findBy(
            $criteria,
            ['createdAt' => 'DESC']
        );

        // Get stats for dashboard badges
        $stats = [
            'total' => $reclamationRepository->count([]),
            'pending' => $reclamationRepository->count(['status' => ReclamationStatus::PENDING]),
            'urgent' => $reclamationRepository->count(['priority' => ReclamationPriority::URGENT, 'status' => [ReclamationStatus::PENDING, ReclamationStatus::IN_PROGRESS]]),
        ];

        return $this->render('admin/reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'stats' => $stats,
            'current_status' => $status,
            'current_priority' => $priority,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_reclamation_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        // Handle Admin Response
        if ($request->isMethod('POST')) {
            // Check if it's a status update or a message
            $newStatus = $request->request->get('status');
            $messageContent = $request->request->get('message');

            if ($newStatus) {
                $statusEnum = ReclamationStatus::tryFrom($newStatus);
                if ($statusEnum) {
                    $reclamation->setStatus($statusEnum);
                    $this->addFlash('success', 'Statut mis à jour avec succès.');
                }
            }

            if ($messageContent) {
                $response = new ReclamationResponse();
                $response->setMessage($messageContent);
                $response->setAuthor($this->getUser());
                $response->setReclamation($reclamation);
                $response->setIsAdminResponse(true);

                // Auto-update status to IN_PROGRESS if we reply to a PENDING one
                if ($reclamation->getStatus() === ReclamationStatus::PENDING) {
                    $reclamation->setStatus(ReclamationStatus::IN_PROGRESS);
                }

                $entityManager->persist($response);
                $this->addFlash('success', 'Réponse envoyée avec succès.');
            }

            $entityManager->flush();
            return $this->redirectToRoute('app_admin_reclamation_show', ['id' => $reclamation->getId()]);
        }

        return $this->render('admin/reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/priority', name: 'app_admin_reclamation_update_priority', methods: ['POST'])]
    public function updatePriority(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $priority = $request->request->get('priority');
        $priorityEnum = ReclamationPriority::tryFrom($priority);

        if ($priorityEnum) {
            $reclamation->setPriority($priorityEnum);
            $entityManager->flush();
            $this->addFlash('success', 'Priorité mise à jour.');
        }

        return $this->redirectToRoute('app_admin_reclamation_show', ['id' => $reclamation->getId()]);
    }
}
