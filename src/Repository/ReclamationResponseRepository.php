<?php

namespace App\Repository;

use App\Entity\ReclamationResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReclamationResponse>
 *
 * @method ReclamationResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReclamationResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReclamationResponse[]    findAll()
 * @method ReclamationResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReclamationResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReclamationResponse::class);
    }
}
