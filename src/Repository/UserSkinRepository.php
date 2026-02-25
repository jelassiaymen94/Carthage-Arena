<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserSkin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSkin>
 */
class UserSkinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSkin::class);
    }

    public function findByUser(User $user, ?string $status = 'active')
    {
        $criteria = ['user' => $user];
        if ($status !== null) {
            $criteria['status'] = $status;
        }
        
        return $this->findBy($criteria, ['purchasedAt' => 'DESC']);
    }
}
