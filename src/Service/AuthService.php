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
        $conn = $this->em->getConnection();
        $userId = $user->getId()->toBinary();

        $conn->beginTransaction();
        try {
            // Lock the user row to serialise concurrent logins for the same account.
            // A second concurrent request blocks here until the first commits.
            $conn->executeQuery(
                'SELECT id FROM `user` WHERE id = ? FOR UPDATE',
                [$userId]
            );

            // Delete any existing token via raw SQL, bypassing the ORM entirely.
            // We do NOT call em->clear() — that would detach the User entity and
            // cause cascade:persist to try re-inserting it.
            $conn->executeStatement(
                'DELETE FROM auth_token WHERE user_id = ?',
                [$userId]
            );

            // Build the new token. Use $user->setAuthToken() to update both sides
            // of the bidirectional OneToOne so Doctrine's identity map is consistent.
            $token = new AuthToken();
            $token->setValue(bin2hex(random_bytes(32)));
            $token->setExpiresAt(new \DateTimeImmutable('+30 days'));
            $user->setAuthToken($token);

            $this->em->persist($token);
            $this->em->flush();

            $conn->commit();

            return $token;
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
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
