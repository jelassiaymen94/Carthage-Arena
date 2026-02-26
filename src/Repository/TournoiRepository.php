<?php

namespace App\Repository;

use App\Entity\Tournoi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tournoi>
 */
class TournoiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tournoi::class);
    }

    /**
     * @return Tournoi[]
     */
    public function findByFilter(?string $filter, ?\App\Entity\User $user, ?string $query = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.dateDebut', 'DESC');

        if ($filter === 'inscribed' && $user) {
            $qb->innerJoin('t.teams', 'team')
                ->innerJoin('team.members', 'membership')
                ->andWhere('membership.player = :user')
                ->setParameter('user', $user);
        } elseif ($filter === 'completed') {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', \App\Enum\TournamentStatus::COMPLETED);
        }

        if ($query) {
            $qb->andWhere('t.nom LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        return $qb->getQuery()->getResult();
    }
    /**
     * Search and filter tournaments.
     *
     * @return Tournoi[]
     */
    public function searchAndFilter(?string $query, ?string $gameId, ?string $status): array
    {
        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.dateDebut', 'DESC');

        if ($query) {
            $qb->andWhere('t.nom LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        if ($gameId && $gameId !== 'all') {
            $qb->andWhere('t.game = :gameId')
                ->setParameter('gameId', $gameId);
        }

        if ($status && $status !== 'all') {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }
}
