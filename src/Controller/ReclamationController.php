<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\ReclamationResponse;
use App\Enum\ReclamationStatus;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reclamations')]
#[IsGranted('ROLE_USER')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamationRepository->findBy(
                ['author' => $this->getUser()],
                ['createdAt' => 'DESC']
            ),
        ]);
    }

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation->setAuthor($this->getUser());
            $entityManager->persist($reclamation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réclamation a été envoyée avec succès.');

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        // specific security check (ensure user owns the reclamation)
        if ($reclamation->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        // Handle new response
        if ($request->isMethod('POST')) {
            $messageContent = $request->request->get('message');

            if ($messageContent) {
                $response = new ReclamationResponse();
                $response->setMessage($messageContent);
                $response->setAuthor($this->getUser());
                $response->setReclamation($reclamation);

                // If it was resolved/closed, reopen it if user replies? 
                // Typically yes, or maybe just set to IN_PROGRESS
                if ($reclamation->getStatus() === ReclamationStatus::RESOLVED || $reclamation->getStatus() === ReclamationStatus::CLOSED) {
                    $reclamation->setStatus(ReclamationStatus::IN_PROGRESS);
                }

                $entityManager->persist($response);
                $entityManager->flush();

                $this->addFlash('success', 'Votre réponse a été ajoutée.');
                return $this->redirectToRoute('app_reclamation_show', ['id' => $reclamation->getId()]);
            }
        }

        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }
}
