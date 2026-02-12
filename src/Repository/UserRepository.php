<?php

namespace App\Repository;

use App\Enum\AccountStatus;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @return User[]
     */
    public function searchAndFilter(?string $search = null, ?string $status = null, ?string $role = null): array
    {
        $qb = $this->createQueryBuilder('u');

        if ($search) {
            $qb->andWhere('u.username LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($status) {
            // Check if status is a valid value for the enum
            $validStatus = false;
            foreach (\App\Enum\AccountStatus::cases() as $case) {
                if ($case->value === $status) {
                    $validStatus = true;
                    break;
                }
            }

            if ($validStatus) {
                $qb->andWhere('u.status = :status')
                   ->setParameter('status', $status);
            }
        }

        if ($role) {
            $qb->andWhere('u.roles LIKE :role')
               ->setParameter('role', '%' . $role . '%');
        }

        return $qb->orderBy('u.createdAt', 'DESC')
                  ->getQuery()
                  ->getResult();
    }
}
