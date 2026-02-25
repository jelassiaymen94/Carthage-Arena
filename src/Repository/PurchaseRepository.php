<?php

namespace App\Repository;

use App\Entity\Purchase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PurchaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchase::class);
    }

    /**
     * Find purchases by user
     */
    public function findByUser($user)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.purchaseDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get total revenue
     */
    public function getTotalRevenue(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('SUM(p.totalPrice)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}