<?php

namespace App\Repository;

use App\Entity\License;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<License>
 */
class LicenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, License::class);
    }

    /**
     * Find an available (unused) license by its code
     */
    public function findAvailableByCode(string $code): ?License
    {
        return $this->createQueryBuilder('l')
            ->where('l.licenseCode = :code')
            ->andWhere('l.isUsed = :isUsed')
            ->setParameter('code', $code)
            ->setParameter('isUsed', false)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Count total licenses
     */
    public function countTotal(): int
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count used licenses
     */
    public function countUsed(): int
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.isUsed = :isUsed')
            ->setParameter('isUsed', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count available licenses
     */
    public function countAvailable(): int
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.isUsed = :isUsed')
            ->setParameter('isUsed', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get all licenses ordered by creation date
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
