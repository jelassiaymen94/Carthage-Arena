<?php

namespace App\Security;

use App\Entity\User;
use App\Enum\AccountStatus;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException('Veuillez v├®rifier votre adresse e-mail avant de vous connecter. Consultez votre bo├«te de r├®ception.');
        }

        if ($user->getStatus() === AccountStatus::SUSPENDED) {
            throw new CustomUserMessageAccountStatusException('Votre compte a ├®t├® suspendu.');
        }

        if ($user->getStatus() === AccountStatus::DELETED) {
            throw new CustomUserMessageAccountStatusException('Ce compte n\'existe plus.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
