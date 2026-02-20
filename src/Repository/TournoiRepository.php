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
    public function findByFilter(?string $filter, ?\App\Entity\User $user): array
    {
        $qb = $this->createQueryBuilder('t');

        if ($filter === 'inscribed' && $user) {
            $qb->innerJoin('t.teams', 'team')
                ->innerJoin('team.members', 'membership')
                ->andWhere('membership.player = :user')
                ->setParameter('user', $user);
        } elseif ($filter === 'completed') {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', \App\Enum\TournamentStatus::COMPLETED);
        }

        return $qb->getQuery()->getResult();
    }
}
