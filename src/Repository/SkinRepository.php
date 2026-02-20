<?php

namespace App\Repository;

use App\Entity\Skin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Skin>
 */
class SkinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Skin::class);
    }

    /**
     * @return Skin[]
     */
    public function search(?string $term, ?string $game, ?string $sort = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.game', 'g');

        if ($term) {
            $qb->andWhere('s.name LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if ($game) {
            $qb->andWhere('g.name = :game')
                ->setParameter('game', $game);
        }

        if ($sort === 'price_asc') {
            $qb->orderBy('s.price', 'ASC');
        } elseif ($sort === 'price_desc') {
            $qb->orderBy('s.price', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }
}
