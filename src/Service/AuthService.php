<?php

namespace App\Service;

use App\Entity\AuthToken;
use App\Entity\User;
use App\Repository\AuthTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

class AuthService
{
    public function __construct(
        private EntityManagerInterface $em,
        private AuthTokenRepository $authTokenRepository,
    ) {
    }

    public function authenticate(User $user): AuthToken
    {
        $existingToken = $this->authTokenRepository->findTokenByUser($user);
        if ($existingToken !== null) {
            $this->revokeToken($existingToken);
        }

        $token = new AuthToken();
        $token->setValue(bin2hex(random_bytes(32)));
        $token->setExpiresAt(new \DateTimeImmutable('+30 days'));
        $token->setUser($user);

        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }

    public function revokeToken(AuthToken $token): void
    {
        $this->em->remove($token);
        $this->em->flush();
    }

    public function revokeUserToken(User $user): void
    {
        $token = $this->authTokenRepository->findTokenByUser($user);
        if ($token !== null) {
            $this->revokeToken($token);
        }
    }

    public function cleanupExpiredTokens(): int
    {
        return $this->authTokenRepository->deleteExpiredTokens();
    }
}
