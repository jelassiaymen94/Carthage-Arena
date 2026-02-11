<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\AuthService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AuthService $authService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $user = $event->getToken()?->getUser();

        if ($user instanceof User) {
            $this->authService->revokeUserToken($user);
        }
    }
}
