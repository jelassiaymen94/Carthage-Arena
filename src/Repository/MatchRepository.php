<?php

namespace App\Repository;

use App\Entity\MatchEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MatchEntity>
 */
class MatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MatchEntity::class);
    }

    /**
     * Find all SCHEDULED matches whose scheduledAt falls on tomorrow.
     *
     * @return MatchEntity[]
     */
    public function findScheduledForTomorrow(): array
    {
        $tomorrow = new \DateTimeImmutable('tomorrow');
        $start = $tomorrow->setTime(0, 0, 0);
        $end = $tomorrow->setTime(23, 59, 59);

        return $this->createQueryBuilder('m')
            ->andWhere('m.status = :status')
            ->andWhere('m.scheduledAt BETWEEN :start AND :end')
            ->setParameter('status', \App\Enum\MatchStatus::SCHEDULED->value)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->join('m.team1', 't1')
            ->join('m.team2', 't2')
            ->join('m.tournoi', 'tr')
            ->addSelect('t1', 't2', 'tr')
            ->getQuery()
            ->getResult();
    }
    /**
     * Count matches scheduled for today.
     */
    public function countTodayMatches(): int
    {
        $start = new \DateTimeImmutable('today');
        $end = $start->modify('+1 day')->modify('-1 second');

        return (int) $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->where('m.scheduledAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
