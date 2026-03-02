<?php

namespace App\Controller\Api;

use App\Entity\Tournoi;
use App\Service\MatchGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class TournoiMatchGenerationController extends AbstractController
{
    public function __construct(
        private readonly MatchGeneratorService $matchGenerator,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Tournoi $data): Tournoi
    {
        // Remove existing matches if any
        foreach ($data->getMatches() as $match) {
            $this->entityManager->remove($match);
        }
        $this->entityManager->flush();

        // Generate new matches
        $matches = $this->matchGenerator->generateMatches($data);

        // Persist all matches
        foreach ($matches as $match) {
            $this->entityManager->persist($match);
        }
        $this->entityManager->flush();

        return $data;
    }
}
