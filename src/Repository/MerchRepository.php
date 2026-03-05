<?php
namespace App\Repository;

use App\Entity\Merch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
            $qb->andWhere('m.name LIKE :term OR m.type LIKE :term')
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

    /**
     * @return array Returns an array of top selling merch with their sales count
     */
    public function getTopMerch(int $limit = 3): array
    {
        return $this->createQueryBuilder('m')
            ->select('m as merch, COALESCE(SUM(p.quantity), 0) as sales')
            ->leftJoin('App\Entity\Purchase', 'p', 'WITH', 'p.merch = m')
            ->groupBy('m.id')
            ->orderBy('sales', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
