<?php

namespace App\Repository;

use App\Entity\Merch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Merch>
 */
class MerchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Merch::class);
    }

    /**
     * @return Merch[]
     */
    public function search(?string $term, ?string $game, ?string $sort = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.game', 'g');

        if ($term) {
            $qb->andWhere('m.name LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if ($game) {
            $qb->andWhere('g.name = :game')
                ->setParameter('game', $game);
        }

        if ($sort === 'price_asc') {
            $qb->orderBy('m.price', 'ASC');
        } elseif ($sort === 'price_desc') {
            $qb->orderBy('m.price', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }
}
