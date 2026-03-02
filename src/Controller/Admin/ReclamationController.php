<?php

namespace App\Controller\Admin;

use App\Entity\Reclamation;
use App\Entity\ReclamationResponse;
use App\Enum\ReclamationCategory;
use App\Enum\ReclamationPriority;
use App\Enum\ReclamationStatus;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\ReclamationAiService;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/admin/reclamations')]
#[IsGranted('ROLE_ADMIN')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'app_admin_reclamation_index', methods: ['GET'])]
    public function index(
        ReclamationRepository $reclamationRepository,
        Request $request,
        ChartBuilderInterface $chartBuilder,
        ReclamationAiService $aiService
    ): Response {
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

        // --- AI Insights (External API) ---
        $aiSummary = $aiService->getSummary($reclamations);

        // --- Analytics Charts ---

        // 1. Status Distribution (Doughnut)
        $statusChart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $statusLabels = [];
        $statusData = [];
        $statusColors = [
            '#9ca3af', // gray-400 (pending)
            '#60a5fa', // blue-400 (in_progress)
            '#4ade80', // green-400 (resolved)
            '#f87171', // red-400 (closed)
        ];

        foreach (ReclamationStatus::cases() as $case) {
            $statusLabels[] = $case->getLabel();
            $statusData[] = $reclamationRepository->count(['status' => $case]);
        }

        $statusChart->setData([
            'labels' => $statusLabels,
            'datasets' => [
                [
                    'backgroundColor' => $statusColors,
                    'borderColor' => 'rgba(255, 255, 255, 0.05)',
                    'data' => $statusData,
                ],
            ],
        ]);
        $statusChart->setOptions([
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['position' => 'bottom', 'labels' => ['color' => '#9ca3af', 'usePointStyle' => true]],
            ],
        ]);

        // 2. Category distribution (Bar)
        $categoryChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $catLabels = [];
        $catData = [];

        foreach (ReclamationCategory::cases() as $case) {
            $catLabels[] = $case->getLabel();
            $catData[] = $reclamationRepository->count(['category' => $case]);
        }

        $categoryChart->setData([
            'labels' => $catLabels,
            'datasets' => [
                [
                    'label' => 'Réclamations par catégorie',
                    'backgroundColor' => '#facc15', // yellow-400 (Carthage Arena brand color)
                    'borderColor' => '#facc15',
                    'data' => $catData,
                ],
            ],
        ]);
        $categoryChart->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['color' => '#9ca3af', 'stepSize' => 1],
                    'grid' => ['color' => 'rgba(255, 255, 255, 0.05)'],
                ],
                'x' => [
                    'ticks' => ['color' => '#9ca3af'],
                    'grid' => ['display' => false],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ]);

        // 3. Evolution Chart (Line)
        $evolutionChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $evoLabels = [];
        $evoData = [];

        // Get data for the last 14 days
        for ($i = 13; $i >= 0; $i--) {
            $date = (new \DateTimeImmutable())->modify("-$i days");
            $dateStr = $date->format('Y-m-d');
            $evoLabels[] = $date->format('d/m');

            // Count reclamations for this day
            $start = $date->setTime(0, 0, 0);
            $end = $date->setTime(23, 59, 59);

            $count = $reclamationRepository->createQueryBuilder('r')
                ->select('COUNT(r.id)')
                ->where('r.createdAt >= :start')
                ->andWhere('r.createdAt <= :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end)
                ->getQuery()
                ->getSingleScalarResult();

            $evoData[] = $count;
        }

        $evolutionChart->setData([
            'labels' => $evoLabels,
            'datasets' => [
                [
                    'label' => 'Nouvelles réclamations',
                    'backgroundColor' => 'rgba(250, 204, 21, 0.1)',
                    'borderColor' => '#facc15',
                    'data' => $evoData,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
        ]);
        $evolutionChart->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['color' => '#9ca3af', 'stepSize' => 1],
                    'grid' => ['color' => 'rgba(255, 255, 255, 0.05)'],
                ],
                'x' => [
                    'ticks' => ['color' => '#9ca3af'],
                    'grid' => ['display' => false],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ]);

        return $this->render('admin/reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'stats' => $stats,
            'current_status' => $status,
            'current_priority' => $priority,
            'statusChart' => $statusChart,
            'categoryChart' => $categoryChart,
            'evolutionChart' => $evolutionChart,
            'aiSummary' => $aiSummary,
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
