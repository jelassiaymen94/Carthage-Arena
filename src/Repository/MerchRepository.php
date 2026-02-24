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
     * Search merch by name or type
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.name LIKE :q OR m.type LIKE :q')
            ->setParameter('q', '%'.$query.'%')
            ->getQuery()
            ->getResult();
    }
}